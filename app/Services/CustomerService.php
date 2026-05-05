<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerService
{
    public function list(int $companyId, array $filters = [])
    {
        return Customer::query()
            ->forCompany($companyId)
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', strtoupper($status)))
            ->when($filters['customer_type'] ?? null, fn($q, $type) => $q->where('customer_type', strtoupper($type)))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('customer_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('business_name', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderBy('customer_type')
            ->orderBy('business_name')
            ->orderBy('first_name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(int $companyId, array $data): Customer
    {
        if (empty($data['customer_code'])) {
            $data['customer_code'] = $this->generateCustomerCode($companyId);
        }

        $data['company_id'] = $companyId;
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $data['balance'] = 0;
        $data['status'] = $data['status'] ?? 'ACTIVE';

        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    public function findForCompany(int $companyId, int $customerId): Customer
    {
        return Customer::query()
            ->forCompany($companyId)
            ->where('customer_id', $customerId)
            ->firstOrFail();
    }

    public function findByTaxId(int $companyId, string $taxId): ?Customer
    {
        $taxId = preg_replace('/[^0-9]/', '', $taxId);

        return Customer::query()
            ->forCompany($companyId)
            ->where('tax_id', $taxId)
            ->first();
    }

    private function generateCustomerCode(int $companyId): string
    {
        $next = Customer::query()
            ->forCompany($companyId)
            ->count() + 1;

        return 'CUST-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}

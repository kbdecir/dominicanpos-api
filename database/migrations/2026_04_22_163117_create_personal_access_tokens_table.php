<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecf_environments', function (Blueprint $table) {
            $table->id('ecf_environment_id');
            $table->enum('code', ['TEST', 'PROD'])->unique();
            $table->string('name', 100);
            $table->string('dgii_base_url', 255);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
        });

        Schema::create('ecf_companies', function (Blueprint $table) {
            $table->id('ecf_company_id');
            $table->unsignedBigInteger('external_company_id');
            $table->string('rnc', 20);
            $table->string('legal_name', 200);
            $table->string('trade_name', 200)->nullable();
            $table->enum('environment_code', ['TEST', 'PROD'])->default('TEST');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique('external_company_id', 'uniq_ecf_company_external');
            $table->unique(['rnc', 'environment_code'], 'uniq_ecf_company_rnc_env');
        });

        Schema::create('ecf_certificates', function (Blueprint $table) {
            $table->id('ecf_certificate_id');
            $table->unsignedBigInteger('ecf_company_id');
            $table->string('certificate_name', 150);
            $table->string('certificate_path', 255);
            $table->text('certificate_password_encrypted');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'EXPIRED'])->default('ACTIVE');
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('ecf_company_id', 'fk_ecf_cert_company')
                ->references('ecf_company_id')
                ->on('ecf_companies');
        });

        Schema::create('ecf_document_types', function (Blueprint $table) {
            $table->id('ecf_document_type_id');
            $table->string('code', 5)->unique();
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->boolean('requires_customer_tax_id')->default(false);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
        });

        Schema::create('ecf_documents', function (Blueprint $table) {
            $table->id('ecf_document_id');

            $table->unsignedBigInteger('ecf_company_id');
            $table->unsignedBigInteger('ecf_document_type_id');

            $table->unsignedBigInteger('external_sale_id')->nullable();
            $table->unsignedBigInteger('external_credit_note_id')->nullable();

            $table->string('document_number', 30);
            $table->string('ncf', 30);

            $table->string('buyer_tax_id', 20)->nullable();
            $table->string('buyer_name', 200)->nullable();
            $table->string('buyer_email', 190)->nullable();

            $table->dateTime('issue_datetime');
            $table->char('currency_code', 3)->default('DOP');

            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);

            $table->string('xml_unsigned_path', 255)->nullable();
            $table->string('xml_signed_path', 255)->nullable();

            $table->string('security_code', 100)->nullable();
            $table->string('track_id', 100)->nullable();

            $table->enum('status', [
                'DRAFT',
                'XML_GENERATED',
                'SIGNED',
                'SENT',
                'ACCEPTED',
                'REJECTED',
                'ERROR',
                'VOIDED',
            ])->default('DRAFT');

            $table->string('dgii_response_code', 50)->nullable();
            $table->text('dgii_response_message')->nullable();

            $table->dateTime('sent_at')->nullable();
            $table->dateTime('response_at')->nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('ecf_company_id', 'fk_ecf_doc_company')
                ->references('ecf_company_id')
                ->on('ecf_companies');

            $table->foreign('ecf_document_type_id', 'fk_ecf_doc_type')
                ->references('ecf_document_type_id')
                ->on('ecf_document_types');

            $table->unique(['ecf_company_id', 'ncf'], 'uniq_ecf_company_ncf');
            $table->index('external_sale_id', 'idx_ecf_external_sale');
            $table->index('status', 'idx_ecf_status');
        });

        Schema::create('ecf_document_items', function (Blueprint $table) {
            $table->id('ecf_document_item_id');

            $table->unsignedBigInteger('ecf_document_id');

            $table->unsignedBigInteger('external_product_id')->nullable();
            $table->string('sku', 60)->nullable();
            $table->string('product_name', 180);

            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_price', 18, 4);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('line_total', 18, 4);

            $table->foreign('ecf_document_id', 'fk_ecf_doc_item_doc')
                ->references('ecf_document_id')
                ->on('ecf_documents');
        });

        Schema::create('ecf_logs', function (Blueprint $table) {
            $table->id('ecf_log_id');

            $table->unsignedBigInteger('ecf_document_id')->nullable();

            $table->string('event_type', 60);
            $table->string('status', 60);

            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->text('error_message')->nullable();

            $table->dateTime('created_at')->useCurrent();

            $table->foreign('ecf_document_id', 'fk_ecf_logs_document')
                ->references('ecf_document_id')
                ->on('ecf_documents');

            $table->index('ecf_document_id', 'idx_ecf_logs_document');
            $table->index('event_type', 'idx_ecf_logs_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecf_logs');
        Schema::dropIfExists('ecf_document_items');
        Schema::dropIfExists('ecf_documents');
        Schema::dropIfExists('ecf_document_types');
        Schema::dropIfExists('ecf_certificates');
        Schema::dropIfExists('ecf_companies');
        Schema::dropIfExists('ecf_environments');
    }
};

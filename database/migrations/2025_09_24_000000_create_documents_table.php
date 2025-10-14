<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->enum('doc_type', ['resume','medical','mdr','other'])->index();
            $table->string('file_path');
            $table->unsignedInteger('version')->default(1);
            $table->text('notes')->nullable();

            $table->enum('status', ['submitted','approved','rejected'])->default('submitted')->index();
            $table->enum('visibility', ['employee','hr','supervisor','hr_supervisor','private_employee'])->default('employee');
            $table->date('expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id','doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

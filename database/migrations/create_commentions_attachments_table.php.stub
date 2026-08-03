<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('commentions.tables.comment_attachments', 'comment_attachments'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained(config('commentions.tables.comments'))->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('commentions.tables.comment_attachments', 'comment_attachments'));
    }
};

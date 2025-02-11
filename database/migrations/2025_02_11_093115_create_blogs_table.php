<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string( 'main_title' )->nullable();
            $table->string( 'subtitle' )->nullable();
            $table->LONGTEXT( 'text' )->nullable();
            $table->string( 'image' )->nullable();
            $table->string( 'meta_title' )->nullable();
            $table->string( 'meta_desc' )->nullable();
            $table->timestamp( 'publish_date' )->nullable();
            $table->tinyInteger( 'type' )->default(1);
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });

        Schema::create('blog_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->onUpdate('restrict')->onDelete('cascade');
            $table->string( 'path' )->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->onUpdate('restrict')->onDelete('cascade');
            $table->string( 'tag' )->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_images');
        Schema::dropIfExists('blogs');
    }
}

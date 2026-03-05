<?php   
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('page_contents', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('page_id')->nullable();
      $table->unsignedBigInteger('subpage_id')->nullable();

      $table->unsignedInteger('schema_version')->default(1);
      $table->json('content')->nullable(); // pole bloků
      $table->timestamps();

      $table->index(['page_id']);
      $table->index(['subpage_id']);

      // (volitelně) FK, pokud máš FK všude jinde:
      // $table->foreign('page_id')->references('id')->on('pages')->nullOnDelete();
      // $table->foreign('subpage_id')->references('id')->on('subpages')->nullOnDelete();

      // zabrání duplicitě (1 obsah na page/subpage):
      $table->unique(['page_id']);
      $table->unique(['subpage_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('page_contents');
  }
};

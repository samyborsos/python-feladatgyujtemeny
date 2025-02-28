use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
/**
* Run the migrations.
*/
public function up(): void
{
Schema::table('questions', function (Blueprint $table) {
$table->index('title_hu');
$table->index('source');
$table->index('difficulty');
$table->index('created_at');
});
}

/**
* Reverse the migrations.
*/
public function down(): void
{
Schema::table('questions', function (Blueprint $table) {
$table->dropIndex('title_hu');
$table->dropIndex('source');
$table->dropIndex('difficulty');
$table->dropIndex('created_at');
});
}
};
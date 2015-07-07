<?php

/*
 |--------------------------------------------------------------------------
 | Update Eminem's tables
 |--------------------------------------------------------------------------
 |
 | When upgrading from 0.6 to 0.7, it is required to upgrade your database
 | tables as well. The structure itself didn't change, which means it isn't
 | necessary to add a migration to the queue. This will run once and
 | destroy traces of itself within the migrations table.
 |
 | To upgrade, run the following Artisan command:
 |
 | php artisan migrate --path=vendor/clumsy/eminem/src/migrations/upgrade
 |
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class UpdateEminemTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('media', function(Blueprint $table)
		{
			$table->dropColumn('path_type');
		});

		Schema::table('media', function(Blueprint $table)
		{
			$table->string('path_type')->default('public');
		});

	    Schema::table('media_associations', function($table)
	    {
	        $table->dropForeign('media_associations_media_id_foreign');
	        $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
	    });

	    App::shutdown(function()
	    {
	    	DB::table('migrations')->where('migration', '2015_07_05_095049_update_eminem_tables')->delete();
	    });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {}

}

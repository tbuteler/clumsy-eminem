<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
			$table->enum('path_type', array('public', 'routed'))->default('public');
		});

	    Schema::table('media_associations', function($table)
	    {
	        $table->dropForeign('media_associations_media_id_foreign');
	        $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
	    });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('media', function(Blueprint $table)
		{
			$table->dropColumn('path_type');
			$table->enum('path_type', array('absolute', 'relative'));
		});

	    Schema::table('media_associations', function($table)
	    {
	        $table->dropForeign('media_associations_media_id_foreign');
	        $table->foreign('media_id')->references('id')->on('media');
	    });
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaAssociationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media_associations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('media_id')->unsigned();
			$table->string('media_association_type');
			$table->integer('media_association_id')->unsigned();
			$table->string('position')->nullable()->default(null);
			$table->integer('order')->unsigned()->nullable()->default(null);
			$table->text('meta')->nullable()->default(null);

			$table->foreign('media_id')->references('id')->on('media');
			$table->index('media_association_type');
			$table->index('media_association_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('media_associations', function($table)
		{
			$table->dropForeign('media_associations_media_id_foreign');
		});

		Schema::drop('media_associations');
	}

}

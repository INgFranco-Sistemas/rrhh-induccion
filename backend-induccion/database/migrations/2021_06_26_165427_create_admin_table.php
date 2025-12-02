<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('admin', function (Blueprint $table) {
			$table->id();
			$table->string('adm_name');
			$table->string('adm_lastname');
			$table->string('adm_inicial')->nullable();
			$table->string('adm_email')->nullable();
			$table->string('password')->nullable();
			$table->integer('adm_estado')->nullable();
			$table->string('adm_cargo')->nullable();
			$table->string('adm_correo')->nullable();
			$table->integer('depe_id')->default(0);// sólo iniciamos en cero, xq permitirar elijir en la unidad donde tramitará
			$table->date('adm_vigencia')->nullable();
			$table->string('adm_observacion')->nullable();
			$table->integer('adm_tipo')->nullable();
			$table->integer('adm_caseta')->nullable();
			$table->integer('adm_esjefe')->nullable();
			$table->string('adm_correo_personal')->nullable();
			$table->string('adm_telefono')->nullable();
			$table->string('adm_direccion')->nullable();
			$table->string('adm_dni')->nullable();
			$table->string('adm_con_especialidad')->nullable();
			$table->integer('tipousuario')->default(1);//1:SGD(funcionarios y servidores),2:terceros
			$table->string('push_id')->nullable();
			$table->integer('adm_primer_logeo')->nullable();
			$table->text('avatar')->default('avatar/logo.png');
			$table->string('darkmode')->nullable();
			$table->rememberToken();
			$table->timestamp('email_verified_at')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('admin');
	}
}

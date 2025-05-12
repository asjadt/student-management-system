<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->nullable();
            $table->enum('send_time', ['before_expiry', 'after_expiry'])->nullable();
            $table->integer('frequency_after_first_reminder')->nullable();
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update')->nullable();
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder'])->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}

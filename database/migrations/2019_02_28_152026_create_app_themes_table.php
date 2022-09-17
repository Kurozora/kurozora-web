<?php

use App\Enums\StatusBarStyle;
use App\Enums\VisualEffectViewStyle;
use App\Models\AppTheme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(AppTheme::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('download_count')->default(0);
            $table->string('version')->default('1.0');

            $table->unsignedTinyInteger('ui_status_bar_style')->default(StatusBarStyle::Default);
            $table->unsignedTinyInteger('ui_visual_effect_view')->default(VisualEffectViewStyle::Dark);

            $table->string('global_background_color');
            $table->string('global_tinted_background_color');
            $table->string('global_bar_tint_color');
            $table->string('global_bar_title_text_color');
            $table->string('global_blur_background_color');
            $table->string('global_border_color');
            $table->string('global_text_color');
            $table->string('global_text_field_background_color');
            $table->string('global_text_field_text_color');
            $table->string('global_text_field_placeholder_text_color');
            $table->string('global_tint_color');
            $table->string('global_tinted_button_text_color');
            $table->string('global_separator_color');
            $table->string('global_separator_color_light');
            $table->string('global_sub_text_color');

            $table->string('table_view_cell_background_color');
            $table->string('table_view_cell_title_text_color');
            $table->string('table_view_cell_sub_text_color');
            $table->string('table_view_cell_chevron_color');
            $table->string('table_view_cell_selected_background_color');
            $table->string('table_view_cell_selected_title_text_color');
            $table->string('table_view_cell_selected_sub_text_color');
            $table->string('table_view_cell_selected_chevron_color');
            $table->string('table_view_cell_action_default_color');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(AppTheme::TABLE_NAME);
    }
};

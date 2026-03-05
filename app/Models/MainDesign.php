<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainDesign extends Model
{
    use HasFactory;

    protected $table = 'main_design';

    /**
     * Eager-loading + header + footer
     */
    protected $with = [
        'colors',
        'buttons',
        'fonts',
        'radius',
        'inputs',
        'indentation',
        'socials',
        'header', // ✅
        'footer', // ✅ NEW
    ];

    /**
     * Skryjeme relační objekty v JSONu (aby se to "neduplikovalo").
     */
    protected $hidden = [
        'colors',
        'buttons',
        'fonts',
        'radius',
        'inputs',
        'indentation',
        'socials',
        'header', // ✅
        'footer', // ✅ NEW
    ];

    /**
     * Appends (flat atributy do mainDesign.xxx)
     */
    protected $appends = [
        // ✅ header
        'logo_url',
        'logo_width',
        'logo_height',
        'header_background',
        'header_color',
        'header_color_hover',
        'submenu_background',
        'submenu_color',
        'submenu_hover',

        // ✅ footer
        'footer_background',
        'footer_color',
        'footer_columns',   
        'footer_content',

        // colors
        'primary_color', 'secondary_color', 'tertiary_color', 'quaternary_color', 'quinary_color',

        // buttons
        'button_type', 'button_color', 'button_text_color', 'button_hover_color', 'button_hover_text_color',
        'button_border_enabled', 'button_border_width', 'button_border_color', 'button_border_hover_color',
        'button_font_weight',

        // fonts
        'font_type', 'font_type_2', 'font_type_3',

        // radius
        'top_left_radius', 'top_right_radius', 'bottom_left_radius', 'bottom_right_radius',

        // inputs
        'input_background_color', 'input_text_color', 'input_border_enabled',
        'input_border_width', 'input_border_color', 'input_radius', 'input_font_weight',
        'input_hover_border_color', 'input_focus_ring_enabled', 'input_focus_ring_color', 'input_focus_ring_width',

        // indentation
        'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
        'margin_top', 'margin_right', 'margin_bottom', 'margin_left',

        // socials
        'social_instagram', 'social_fb', 'social_linkedin', 'social_tiktok', 'social_x', 'social_whatsapp',
    ];

    /**
     * Fillable (logo_url už není v main_design)
     */
    protected $fillable = [
        'colors_id',
        'buttons_id',
        'fonts_id',
        'radius_id',
        'inputs_id',
        'indentation_id',
        'socials_id',
        'footer_id', // ✅ NEW
    ];

    // ===== RELATIONS =====
    public function colors()      { return $this->belongsTo(MainDesignColor::class, 'colors_id'); }
    public function buttons()     { return $this->belongsTo(MainDesignButton::class, 'buttons_id'); }
    public function fonts()       { return $this->belongsTo(MainDesignFont::class, 'fonts_id'); }
    public function radius()      { return $this->belongsTo(MainDesignRadius::class, 'radius_id'); }
    public function inputs()      { return $this->belongsTo(MainDesignInput::class, 'inputs_id'); }
    public function indentation() { return $this->belongsTo(MainDesignIndentation::class, 'indentation_id'); }
    public function socials()     { return $this->belongsTo(MainDesignSocial::class, 'socials_id'); }

    // ✅ header (hasOne přes main_design_id)
    public function header()
    {
        return $this->hasOne(MainDesignHeader::class, 'main_design_id');
    }

    // ✅ footer (belongsTo přes footer_id)
    public function footer()
    {
        return $this->belongsTo(MainDesignFooter::class, 'footer_id');
    }

    // ===== HELPERS =====
    private function relVal($rel, $key, $default = null)
    {
        return $this->$rel?->$key ?? $default;
    }

    // ===== ACCESSORS (header) =====
    public function getLogoUrlAttribute()            { return $this->relVal('header', 'logo_url'); }
    public function getLogoWidthAttribute()          { return $this->relVal('header', 'logo_width'); }
    public function getLogoHeightAttribute()         { return $this->relVal('header', 'logo_height'); }
    public function getHeaderBackgroundAttribute()  { return $this->relVal('header', 'header_background'); }
    public function getHeaderColorAttribute()       { return $this->relVal('header', 'header_color'); }
    public function getHeaderColorHoverAttribute()  { return $this->relVal('header', 'header_color_hover'); }
    public function getSubmenuBackgroundAttribute() { return $this->relVal('header', 'submenu_background'); }
    public function getSubmenuColorAttribute()      { return $this->relVal('header', 'submenu_color'); }
    public function getSubmenuHoverAttribute()      { return $this->relVal('header', 'submenu_hover'); }

    // ===== ACCESSORS (footer) =====
    public function getFooterBackgroundAttribute() { return $this->relVal('footer', 'footer_background'); }
    public function getFooterColorAttribute()      { return $this->relVal('footer', 'footer_color'); }
    public function getFooterColumnsAttribute()    { return $this->relVal('footer', 'footer_columns'); }
    public function getFooterContentAttribute()    { return $this->relVal('footer', 'footer_content', []); }
    
    // ===== ACCESSORS (colors) =====
    public function getPrimaryColorAttribute()    { return $this->relVal('colors', 'primary_color', '#ffffff'); }
    public function getSecondaryColorAttribute()  { return $this->relVal('colors', 'secondary_color', '#000000'); }
    public function getTertiaryColorAttribute()   { return $this->relVal('colors', 'tertiary_color', '#ffffff'); }
    public function getQuaternaryColorAttribute() { return $this->relVal('colors', 'quaternary_color', '#ffffff'); }
    public function getQuinaryColorAttribute()    { return $this->relVal('colors', 'quinary_color', '#ffffff'); }

    // ===== ACCESSORS (buttons) =====
    public function getButtonTypeAttribute()             { return $this->relVal('buttons', 'button_type'); }
    public function getButtonColorAttribute()            { return $this->relVal('buttons', 'button_color'); }
    public function getButtonTextColorAttribute()        { return $this->relVal('buttons', 'button_text_color'); }
    public function getButtonHoverColorAttribute()       { return $this->relVal('buttons', 'button_hover_color'); }
    public function getButtonHoverTextColorAttribute()   { return $this->relVal('buttons', 'button_hover_text_color'); }
    public function getButtonBorderEnabledAttribute()    { return $this->relVal('buttons', 'button_border_enabled'); }
    public function getButtonBorderWidthAttribute()      { return $this->relVal('buttons', 'button_border_width'); }
    public function getButtonBorderColorAttribute()      { return $this->relVal('buttons', 'button_border_color'); }
    public function getButtonBorderHoverColorAttribute() { return $this->relVal('buttons', 'button_border_hover_color'); }
    public function getButtonFontWeightAttribute()       { return $this->relVal('buttons', 'button_font_weight'); }

    // ===== ACCESSORS (fonts) =====
    public function getFontTypeAttribute()  { return $this->relVal('fonts', 'font_type'); }
    public function getFontType2Attribute() { return $this->relVal('fonts', 'font_type_2'); }
    public function getFontType3Attribute() { return $this->relVal('fonts', 'font_type_3'); }

    // ===== ACCESSORS (radius) =====
    public function getTopLeftRadiusAttribute()     { return $this->relVal('radius', 'top_left_radius'); }
    public function getTopRightRadiusAttribute()    { return $this->relVal('radius', 'top_right_radius'); }
    public function getBottomLeftRadiusAttribute()  { return $this->relVal('radius', 'bottom_left_radius'); }
    public function getBottomRightRadiusAttribute() { return $this->relVal('radius', 'bottom_right_radius'); }

    // ===== ACCESSORS (inputs) =====
    public function getInputBackgroundColorAttribute() { return $this->relVal('inputs', 'input_background_color'); }
    public function getInputTextColorAttribute()       { return $this->relVal('inputs', 'input_text_color'); }
    public function getInputBorderEnabledAttribute()   { return $this->relVal('inputs', 'input_border_enabled'); }
    public function getInputBorderWidthAttribute()     { return $this->relVal('inputs', 'input_border_width'); }
    public function getInputBorderColorAttribute()     { return $this->relVal('inputs', 'input_border_color'); }
    public function getInputRadiusAttribute()          { return $this->relVal('inputs', 'input_radius'); }
    public function getInputFontWeightAttribute()      { return $this->relVal('inputs', 'input_font_weight'); }

    public function getInputHoverBorderColorAttribute() { return $this->relVal('inputs', 'input_hover_border_color'); }
    public function getInputFocusRingEnabledAttribute() { return $this->relVal('inputs', 'input_focus_ring_enabled'); }
    public function getInputFocusRingColorAttribute()   { return $this->relVal('inputs', 'input_focus_ring_color'); }
    public function getInputFocusRingWidthAttribute()   { return $this->relVal('inputs', 'input_focus_ring_width'); }

    // ===== ACCESSORS (indentation) =====
    public function getPaddingTopAttribute()    { return $this->relVal('indentation', 'padding_top'); }
    public function getPaddingRightAttribute()  { return $this->relVal('indentation', 'padding_right'); }
    public function getPaddingBottomAttribute() { return $this->relVal('indentation', 'padding_bottom'); }
    public function getPaddingLeftAttribute()   { return $this->relVal('indentation', 'padding_left'); }

    public function getMarginTopAttribute()     { return $this->relVal('indentation', 'margin_top'); }
    public function getMarginRightAttribute()   { return $this->relVal('indentation', 'margin_right'); }
    public function getMarginBottomAttribute()  { return $this->relVal('indentation', 'margin_bottom'); }
    public function getMarginLeftAttribute()    { return $this->relVal('indentation', 'margin_left'); }

    // ===== ACCESSORS (socials) =====
    public function getSocialInstagramAttribute() { return $this->relVal('socials', 'social_instagram'); }
    public function getSocialFbAttribute()        { return $this->relVal('socials', 'social_fb'); }
    public function getSocialLinkedinAttribute()  { return $this->relVal('socials', 'social_linkedin'); }
    public function getSocialTiktokAttribute()    { return $this->relVal('socials', 'social_tiktok'); }
    public function getSocialXAttribute()         { return $this->relVal('socials', 'social_x'); }
    public function getSocialWhatsappAttribute()  { return $this->relVal('socials', 'social_whatsapp'); }
}

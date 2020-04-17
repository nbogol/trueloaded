
<div class="tabbable tabbable-custom box-style-tab">
  <ul class="nav nav-tabs">
{if $responsive && !$settings.data_class}
  <li class="active"><a href="#view-{$id}" data-toggle="tab">{$smarty.const.TEXT_VIEW}</a></li>
    {if $styleHide.font !== 1}
      <li><a href="#font-{$id}" data-toggle="tab">{$smarty.const.TEXT_FONT}</a></li>
    {/if}
{else}
    {if $styleHide.font !== 1}
      <li class="active"><a href="#font-{$id}" data-toggle="tab">{$smarty.const.TEXT_FONT}</a></li>
    {/if}
{/if}
    {if $styleHide.background !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background !== 1} class="active"{/if}><a href="#background-{$id}" data-toggle="tab">{$smarty.const.TEXT_BACKGROUND}</a></li>
    {/if}
    {if $styleHide.padding !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding !== 1} class="active"{/if}><a href="#padding-{$id}" data-toggle="tab">{$smarty.const.TEXT_PADDING}</a></li>
    {/if}
    {if $styleHide.border !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border !== 1} class="active"{/if}><a href="#border-{$id}" data-toggle="tab">{$smarty.const.TEXT_BORDER}</a></li>
    {/if}
    {if $styleHide.size !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size !== 1} class="active"{/if}><a href="#size-{$id}" data-toggle="tab">{$smarty.const.TABLE_HEADING_FILE_SIZE}</a></li>
    {/if}
    {if $styleHide.display !== 1}
      <li {if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size === 1 && $styleHide.display !== 1} class="active"{/if}><a href="#display-{$id}" data-toggle="tab">Display</a></li>
    {/if}

  </ul>
  <div class="tab-content menu-list">
    {if $responsive && !$settings.data_class}
      <div class="tab-pane active" id="view-{$id}">
        <p><label><input type="checkbox" name="{$name}[display_none]"{if $value.display_none} checked{/if}/> {$smarty.const.TEXT_HIDE_BLOCK}</label></p>

        {if $responsive_settings}
          {foreach $responsive_settings as $item}
            {include $item}
          {/foreach}
        {/if}
        {if $block_view}
        {include 'schema.tpl'}
        {/if}

      </div>
    {/if}
    <div class="tab-pane{if ($responsive != 1 || $settings.data_class) && $styleHide.font !== 1} active{/if}" id="font-{$id}">

      {if $styleHide.font.content !== 1 && ($id == 'before' || $id == 'after')}
        <div class="setting-row">
          <label for="">Content</label>
          <input type="text" name="{$name}[content]" value="{$value.content}" class="form-control" />
        </div>
      {/if}

      {if $styleHide.font['font_family'] !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_FAMILY}</label>
        <select name="{$name}[font-family]" id="" class="form-control">
          <option value=""{if $value['font-family'] == ''} selected{/if}></option>
          <option value="Arial"{if $value['font-family'] == 'Arial'} selected{/if}>Arial</option>
          <option value="Verdana"{if $value['font-family'] == 'Verdana'} selected{/if}>Verdana</option>
          <option value="Tahoma"{if $value['font-family'] == 'Tahoma'} selected{/if}>Tahomaa</option>
          <option value="Times"{if $value['font-family'] == 'Times'} selected{/if}>Times</option>
          <option value="Times New Roman"{if $value['font-family'] == 'Times New Roman'} selected{/if}>Times New Roman</option>
          <option value="Georgia"{if $value['font-family'] == 'Georgia'} selected{/if}>Georgia</option>
          <option value="Trebuchet MS"{if $value['font-family'] == 'Trebuchet MS'} selected{/if}>Trebuchet MS</option>
          <option value="Sans"{if $value['font-family'] == 'Sans'} selected{/if}>Sans</option>
          <option value="Comic Sans MS"{if $value['font-family'] == 'Comic Sans MS'} selected{/if}>Comic Sans MS</option>
          <option value="Courier New"{if $value['font-family'] == 'Courier New'} selected{/if}>Courier New</option>
          <option value="Garamond"{if $value['font-family'] == 'Garamond'} selected{/if}>Garamond</option>
          <option value="Helvetica"{if $value['font-family'] == 'Helvetica'} selected{/if}>Helvetica</option>
          {foreach $font_added as $item}
            <option value="{$item}"{if $value['font-family'] == $item} selected{/if}>{$item}</option>
          {/foreach}
        </select>
      </div>
      {/if}

      {if $styleHide.font.color !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_COLOR_}</label>
        <div class="colors-inp">
          <div id="cp3" class="input-group colorpicker-component">
            <input type="text" name="{$name}[color]" value="{$value.color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
      </div>
      {/if}

      {if $styleHide.font.font_size !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_SIZE}</label>
        <input type="number" name="{$name}[font-size]" value="{$value['font-size']}" class="form-control" />
        <select name="{$name}[font_size_measure]" id="" class="form-control sizing" data-name="{$name}[font-size]">
          <option value=""{if $value.font_size_measure == '' || $value.font_size_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.font_size_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.font_size_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.font_size_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.font_size_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.font_size_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.font_size_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.font_size_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.font_weight !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_WEIGHT}</label>
        <select name="{$name}[font-weight]" id="" class="form-control">
          <option value=""{if $value['font-weight'] == ''} selected{/if}></option>
          <option value="100"{if $value['font-weight'] == '100'} selected{/if}>100</option>
          <option value="200"{if $value['font-weight'] == '200'} selected{/if}>200</option>
          <option value="300"{if $value['font-weight'] == '300'} selected{/if}>300</option>
          <option value="400"{if $value['font-weight'] == '400' || $value['font-weight'] == 'normal'} selected{/if}>400 ({$smarty.const.TEXT_NORMAL})</option>
          <option value="500"{if $value['font-weight'] == '500'} selected{/if}>500</option>
          <option value="600"{if $value['font-weight'] == '600'} selected{/if}>600</option>
          <option value="bold"{if $value['font-weight'] == '700' || $value['font-weight'] == 'bold'} selected{/if}>700 ({$smarty.const.TEXT_BOLD})</option>
          <option value="800"{if $value['font-weight'] == '800'} selected{/if}>800</option>
          <option value="900"{if $value['font-weight'] == '900'} selected{/if}>900</option>
          <option value="1000"{if $value['font-weight'] == '1000'} selected{/if}>1000</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.line_height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_LINE_HEIGHT}</label>
        <input type="number" name="{$name}[line-height]" value="{$value['line-height']}" class="form-control" />
        <select name="{$name}[line_height_measure]" class="form-control sizing sizing-line-height" data-name="{$name}[line-height]">
          <option value=""{if $value.line_height_measure == '' || $value.line_height_measure == 'em'} selected{/if}>em</option>
          <option value="px"{if $value.line_height_measure == 'px'} selected{/if}>px</option>
          <option value="%"{if $value.line_height_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.line_height_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.line_height_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.line_height_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.line_height_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.line_height_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_align !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_TEXT_ALIGN}</label>
        <select name="{$name}[text-align]" id="" class="form-control">
          <option value=""{if $value['text-align'] == ''} selected{/if}></option>
          <option value="left"{if $value['text-align'] == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $value['text-align'] == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
          <option value="center"{if $value['text-align'] == 'center'} selected{/if}>{$smarty.const.TEXT_CENTER}</option>
          <option value="justify"{if $value['text-align'] == 'justify'} selected{/if}>{$smarty.const.TEXT_JUSTIFY}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_shadow !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_TEXT_SHADOW}</label>
        <div class="" style="display: inline-block; width: 69%">
          <input type="number" name="{$name}[text_shadow_left]" value="{$value.text_shadow_left}" class="form-control" placeholder="position left" style="margin-bottom: 5px" />
          <select name="{$name}[text_shadow_left_measure]" class="form-control sizing" data-name="{$name}[text_shadow_left]">
            <option value=""{if $value.text_shadow_left_measure == '' || $value.text_shadow_left_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.text_shadow_left_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.text_shadow_left_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.text_shadow_left_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.text_shadow_left_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.text_shadow_left_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.text_shadow_left_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.text_shadow_left_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[text_shadow_top]" value="{$value.text_shadow_top}" class="form-control" placeholder="position top" style="margin-bottom: 5px" />
          <select name="{$name}[text_shadow_top_measure]" class="form-control sizing" data-name="{$name}[text_shadow_top]">
            <option value=""{if $value.text_shadow_top_measure == '' || $value.text_shadow_top_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.text_shadow_top_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.text_shadow_top_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.text_shadow_top_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.text_shadow_top_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.text_shadow_top_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.text_shadow_top_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.text_shadow_top_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[text_shadow_size]" value="{$value.text_shadow_size}" class="form-control" placeholder="radius" />
          <select name="{$name}[text_shadow_size_measure]" class="form-control sizing" data-name="{$name}[text_shadow_size]">
            <option value=""{if $value.text_shadow_size_measure == '' || $value.text_shadow_size_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.text_shadow_size_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.text_shadow_size_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.text_shadow_size_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.text_shadow_size_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.text_shadow_size_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.text_shadow_size_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.text_shadow_size_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <div class="colors-inp">
            <div id="cp3" class="input-group colorpicker-component">
              <input type="text" name="{$name}[text_shadow_color]" value="{$value.text_shadow_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
              <span class="input-group-addon"><i></i></span>
            </div>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.font.vertical_align !== 1}
      <div class="setting-row">
        <label for="">Vertical align</label>
        <select name="{$name}[vertical-align]" id="" class="form-control">
          <option value=""{if $value['vertical-align'] == ''} selected{/if}>baseline</option>
          <option value="bottom"{if $value['vertical-align'] == 'bottom'} selected{/if}>bottom</option>
          <option value="middle"{if $value['vertical-align'] == 'middle'} selected{/if}>middle</option>
          <option value="sub"{if $value['vertical-align'] == 'sub'} selected{/if}>sub</option>
          <option value="super"{if $value['vertical-align'] == 'super'} selected{/if}>super</option>
          <option value="text-bottom"{if $value['vertical-align'] == 'text-bottom'} selected{/if}>text-bottom</option>
          <option value="text-top"{if $value['vertical-align'] == 'text-top'} selected{/if}>text-top</option>
          <option value="top"{if $value['vertical-align'] == 'top'} selected{/if}>top</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_transform !== 1}
      <div class="setting-row">
        <label for="">Transform</label>
        <select name="{$name}[text-transform]" id="" class="form-control">
          <option value=""{if $value['text-transform'] == ''} selected{/if}></option>
          <option value="none"{if $value['text-transform'] == 'none'} selected{/if}>none</option>
          <option value="uppercase"{if $value['text-transform'] == 'uppercase'} selected{/if}>uppercase</option>
          <option value="lowercase"{if $value['text-transform'] == 'lowercase'} selected{/if}>lowercase</option>
          <option value="capitalize"{if $value['text-transform'] == 'capitalize'} selected{/if}>capitalize</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.text_decoration !== 1}
      <div class="setting-row">
        <label for="">Text decoration</label>
        <select name="{$name}[text-decoration]" id="" class="form-control">
          <option value=""{if $value['text-decoration'] == ''} selected{/if}></option>
          <option value="none"{if $value['text-decoration'] == 'none'} selected{/if}>none</option>
          <option value="underline"{if $value['text-decoration'] == 'underline'} selected{/if}>underline</option>
          <option value="line-through"{if $value['text-decoration'] == 'line-through'} selected{/if}>line through</option>
          <option value="overline"{if $value['text-decoration'] == 'overline'} selected{/if}>overline</option>
          <option value="inherit"{if $value['text-decoration'] == 'inherit'} selected{/if}>inherit</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.font_style !== 1}
      <div class="setting-row">
        <label for="">Font Style</label>
        <select name="{$name}[font-style]" id="" class="form-control">
          <option value=""{if $value['font-style'] == ''} selected{/if}></option>
          <option value="normal"{if $value['font-style'] == 'normal'} selected{/if}>normal</option>
          <option value="italic"{if $value['font-style'] == 'italic'} selected{/if}>italic</option>
        </select>
      </div>
      {/if}

      {if $styleHide.font.cursor !== 1}
      <div class="setting-row">
        <label for="">Cursor</label>
        <select name="{$name}[cursor]" id="" class="form-control cursor" style="cursor: help;">
          <option value=""{if $value.cursor == ''} selected{/if}></option>
          <option value="default"{if $value.cursor == 'default'} selected{/if}>default</option>
          <option value="crosshair"{if $value.cursor == 'crosshair'} selected{/if}>crosshair</option>
          <option value="help"{if $value.cursor == 'help'} selected{/if}>help</option>
          <option value="move"{if $value.cursor == 'move'} selected{/if}>move</option>
          <option value="pointer"{if $value.cursor == 'pointer'} selected{/if}>pointer</option>
          <option value="progress"{if $value.cursor == 'progress'} selected{/if}>progress</option>
          <option value="text"{if $value.cursor == 'text'} selected{/if}>text</option>
          <option value="wait"{if $value.cursor == 'wait'} selected{/if}>wait</option>
          <option value="n-resize"{if $value.cursor == 'n-resize'} selected{/if}>n-resize</option>
          <option value="ne-resize"{if $value.cursor == 'ne-resize'} selected{/if}>ne-resize</option>
          <option value="e-resize"{if $value.cursor == 'e-resize'} selected{/if}>e-resize</option>
          <option value="se-resize"{if $value.cursor == 'se-resize'} selected{/if}>se-resize</option>
          <option value="s-resize"{if $value.cursor == 's-resize'} selected{/if}>s-resize</option>
          <option value="sw-resize"{if $value.cursor == 'sw-resize'} selected{/if}>sw-resize</option>
          <option value="w-resize"{if $value.cursor == 'w-resize'} selected{/if}>w-resize</option>
          <option value="nw-resize"{if $value.cursor == 'nw-resize'} selected{/if}>nw-resize</option>
        </select>
      </div>
        <script type="text/javascript">
          $(function(){
            $('.cursor').on('change', function(){
              $(this).css('cursor', $(this).val())
            })
          })
        </script>
      {/if}


    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background !== 1} active{/if}" id="background-{$id}">

      {if $styleHide.background.background_color !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_COLOR}</label>
        <div class="colors-inp">
          <div id="cp2" class="input-group colorpicker-component">
            <input type="text" name="{$name}[background-color]" value="{$value['background-color']}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
      </div>
      {/if}

      {if $styleHide.background.background_image !== 1}
      <div class="setting-row setting-row-image">
        <label for="">{$smarty.const.TEXT_BACKGROUND_IMAGE}</label>

        {if isset($value.background_image)}
          <div class="image">
            <img src="../{\frontend\design\Info::themeImage($value.background_image)}" alt="">
            <div class="remove-img"></div>
          </div>
        {/if}

        <div class="image-upload">
          <div class="upload" data-name="{$name}[background_image]"></div>
          <script type="text/javascript">
            $('#{$id} .upload').uploads().on('upload', function(){
              var img = $('#{$id} .dz-image-preview img', this).attr('src');
              $('#{$id} .demo-box').css('background-image', 'url("'+img+'")')
            });
            $('.popup-box-wrap').on('remove', function(){
              $('#{$id} .upload').trigger('destroy')
            });
            $('.style-tabs-content').on('st_remove', function(){
              $('#{$id} .upload').trigger('destroy')
            });

            $(function(){
              $('#{$id} .setting-row-image .image > img').each(function(){
                var img = $(this).attr('src');
                $('#{$id} .demo-box').css('background-image', 'url("'+img+'")');

                $('#{$id} input[name="{$name}[background_image]"]').val('{$value.background_image}').trigger('change');
              });

              $('#{$id} .setting-row-image .image .remove-img').on('click', function(){
                $('#{$id} input[name="{$name}[background_image]"]').val('').trigger('change');
                $('#{$id} .setting-row-image .image').remove()
              })

            });

          </script>
        </div>

      </div>
      {/if}

      {if $styleHide.background.background_position !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_POSITION}</label>
        <select name="{$name}[background-position]" id="" class="form-control">
          <option value=""{if $value['background-position'] == ''} selected{/if}></option>
          <option value="top left"{if $value['background-position'] == 'top left'} selected{/if}>{$smarty.const.TEXT_TOP_LEFT}</option>
          <option value="top center"{if $value['background-position'] == 'top center'} selected{/if}>{$smarty.const.TEXT_TOP_CENTER}</option>
          <option value="top right"{if $value['background-position'] == 'top right'} selected{/if}>{$smarty.const.TEXT_TOP_RIGHT}</option>
          <option value="left"{if $value['background-position'] == 'left'} selected{/if}>{$smarty.const.TEXT_MIDDLE_LEFT}</option>
          <option value="center"{if $value['background-position'] == 'center'} selected{/if}>{$smarty.const.TEXT_MIDDLE_CENTER}</option>
          <option value="right"{if $value['background-position'] == 'right'} selected{/if}>{$smarty.const.TEXT_MIDDLE_RIGHT}</option>
          <option value="bottom left"{if $value['background-position'] == 'bottom left'} selected{/if}>{$smarty.const.TEXT_BOTTOM_LEFT}</option>
          <option value="bottom center"{if $value['background-position'] == 'bottom center'} selected{/if}>{$smarty.const.TEXT_BOTTOM_CENTER}</option>
          <option value="bottom right"{if $value['background-position'] == 'bottom right'} selected{/if}>{$smarty.const.TEXT_BOTTOM_RIGHT}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.background.background_repeat !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_REPEAT}</label>
        <select name="{$name}[background-repeat]" id="" class="form-control">
          <option value=""{if $value['background-repeat'] == ''} selected{/if}></option>
          <option value="no-repeat"{if $value['background-repeat'] == 'no-repeat'} selected{/if}>{$smarty.const.TEXT_NO_REPEAT}</option>
          <option value="repeat"{if $value['background-repeat'] == 'repeat'} selected{/if}>{$smarty.const.TEXT_REPEAT}</option>
          <option value="repeat-x"{if $value['background-repeat'] == 'repeat-x'} selected{/if}>{$smarty.const.TEXT_REPEAT_HORIZONTAL}</option>
          <option value="repeat-y"{if $value['background-repeat'] == 'repeat-y'} selected{/if}>{$smarty.const.TEXT_REPEAT_VERTICAL}</option>
          <option value="space"{if $value['background-repeat'] == 'space'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE}</option>
          <option value="top left"{if $value['background-repeat'] == 'top left'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE_RESIZE}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.background.background_size !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_SIZE}</label>
        <select name="{$name}[background-size]" id="" class="form-control">
          <option value=""{if $value['background-size'] == ''} selected{/if}>{$smarty.const.TEXT_NO_RESIZE}</option>
          <option value="cover"{if $value['background-size'] == 'cover'} selected{/if}>{$smarty.const.TEXT_FIELD_ALL_BLOCK}</option>
          <option value="contain"{if $value['background-size'] == 'contain'} selected{/if}>{$smarty.const.TEXT_WIDTH_HEIGHT_SIZE}</option>
        </select>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding !== 1} active{/if}" id="padding-{$id}">

      {if $styleHide.padding.padding_top !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_TOP}</label>
        <input type="number" name="{$name}[padding-top]" value="{$value['padding-top']}" class="form-control" />
        <select name="{$name}[padding_top_measure]" class="form-control sizing" data-name="{$name}[padding-top]">
          <option value=""{if $value.padding_top_measure == '' || $value.padding_top_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.padding_top_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.padding_top_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.padding_top_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.padding_top_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.padding_top_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.padding_top_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.padding_top_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.padding_left !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_LEFT}</label>
        <input type="number" name="{$name}[padding-left]" value="{$value['padding-left']}" class="form-control" />
        <select name="{$name}[padding_left_measure]" class="form-control sizing" data-name="{$name}[padding-left]">
          <option value=""{if $value.padding_left_measure == '' || $value.padding_left_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.padding_left_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.padding_left_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.padding_left_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.padding_left_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.padding_left_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.padding_left_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.padding_left_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.padding_right !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_RIGHT}</label>
        <input type="number" name="{$name}[padding-right]" value="{$value['padding-right']}" class="form-control" />
        <select name="{$name}[padding_right_measure]" class="form-control sizing" data-name="{$name}[padding-right]">
          <option value=""{if $value.padding_right_measure == '' || $value.padding_right_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.padding_right_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.padding_right_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.padding_right_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.padding_right_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.padding_right_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.padding_right_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.padding_right_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.padding_bottom !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_BOTTOM}</label>
        <input type="number" name="{$name}[padding-bottom]" value="{$value['padding-bottom']}" class="form-control" />
        <select name="{$name}[padding_bottom_measure]" class="form-control sizing" data-name="{$name}[padding-bottom]">
          <option value=""{if $value.padding_bottom_measure == '' || $value.padding_bottom_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.padding_bottom_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.padding_bottom_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.padding_bottom_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.padding_bottom_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.padding_bottom_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.padding_bottom_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.padding_bottom_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.margin_top !== 1}
      <div class="setting-row">
        <label for="">Margin Top</label>
        <input type="number" name="{$name}[margin-top]" value="{$value['margin-top']}" class="form-control" />
        <select name="{$name}[margin_top_measure]" class="form-control sizing" data-name="{$name}[margin-top]">
          <option value=""{if $value.margin_top_measure == '' || $value.margin_top_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.margin_top_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.margin_top_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.margin_top_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.margin_top_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.margin_top_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.margin_top_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.margin_top_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.margin_left !== 1}
      <div class="setting-row">
        <label for="">Margin left</label>
        <input type="number" name="{$name}[margin-left]" value="{$value['margin-left']}" class="form-control" />
        <select name="{$name}[margin_left_measure]" class="form-control sizing" data-name="{$name}[margin-left]">
          <option value=""{if $value.margin_left_measure == '' || $value.margin_left_measure == 'px'} selected{/if}>px</option>
          <option value="auto"{if $value.margin_left_measure == 'auto'} selected{/if}>auto</option>
          <option value="em"{if $value.margin_left_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.margin_left_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.margin_left_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.margin_left_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.margin_left_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.margin_left_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.margin_left_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.margin_right !== 1}
      <div class="setting-row">
        <label for="">Margin right</label>
        <input type="number" name="{$name}[margin-right]" value="{$value['margin-right']}" class="form-control" />
        <select name="{$name}[margin_right_measure]" class="form-control sizing" data-name="{$name}[margin-right]">
          <option value=""{if $value.margin_right_measure == '' || $value.margin_right_measure == 'px'} selected{/if}>px</option>
          <option value="auto"{if $value.margin_right_measure == 'auto'} selected{/if}>auto</option>
          <option value="em"{if $value.margin_right_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.margin_right_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.margin_right_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.margin_right_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.margin_right_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.margin_right_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.margin_right_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.padding.margin_bottom !== 1}
      <div class="setting-row">
        <label for="">Margin bottom</label>
        <input type="number" name="{$name}[margin-bottom]" value="{$value['margin-bottom']}" class="form-control" />
        <select name="{$name}[margin_bottom_measure]" class="form-control sizing" data-name="{$name}[margin-bottom]">
          <option value=""{if $value.margin_bottom_measure == '' || $value.margin_bottom_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.margin_bottom_measure == 'em'} selected{/if}>em</option>
          <option value="pr"{if $value.margin_bottom_measure == 'pr'} selected{/if}>%</option>
          <option value="rem"{if $value.margin_bottom_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.margin_bottom_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.margin_bottom_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.margin_bottom_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.margin_bottom_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border !== 1} active{/if}" id="border-{$id}">

      {if $styleHide.border.border_top !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_TOP}</label>
        <input type="number" name="{$name}[border-top-width]" value="{$value['border-top-width']}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border-top-color]" value="{$value['border-top-color']}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 0 0 0 1px">{$smarty.const.TEXT_CLICK_CHOOSE_COLOR}</span>
      </div>
      {/if}

      {if $styleHide.border.border_left !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_LEFT}</label>
        <input type="number" name="{$name}[border-left-width]" value="{$value['border-left-width']}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border-left-color]" value="{$value['border-left-color']}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.border.border_right !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_RIGHT}</label>
        <input type="number" name="{$name}[border-right-width]" value="{$value['border-right-width']}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border-right-color]" value="{$value['border-right-color']}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.border.border_bottom !== 1}
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_BOTTOM}</label>
        <input type="number" name="{$name}[border-bottom-width]" value="{$value['border-bottom-width']}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="{$name}[border-bottom-color]" value="{$value['border-bottom-color']}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-addon"><i></i></span>
          </div>
        </div>
      </div>
      {/if}

      {if $styleHide.border.border_radius !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BORDER_RADIUS}</label>
        <div class="" style="display: inline-block; width: 69%">
          <input type="number" name="{$name}[border-top-left-radius]" value="{$value['border-top-left-radius']}" class="form-control" placeholder="top left" style="margin-bottom: 5px" />
          <select name="{$name}[border_radius_1_measure]" class="form-control sizing" data-name="{$name}[border-top-left-radius]">
            <option value=""{if $value.border_radius_1_measure == '' || $value.border_radius_1_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.border_radius_1_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.border_radius_1_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.border_radius_1_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.border_radius_1_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.border_radius_1_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.border_radius_1_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.border_radius_1_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[border-top-right-radius]" value="{$value['border-top-right-radius']}" class="form-control" placeholder="top right" style="margin-bottom: 5px" />
          <select name="{$name}[border_radius_2_measure]" class="form-control sizing" data-name="{$name}[border-top-right-radius]">
            <option value=""{if $value.border_radius_2_measure == '' || $value.border_radius_2_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.border_radius_2_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.border_radius_2_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.border_radius_2_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.border_radius_2_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.border_radius_2_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.border_radius_2_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.border_radius_2_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[border-bottom-left-radius]" value="{$value['border-bottom-left-radius']}" class="form-control" placeholder="bottom left" />
          <select name="{$name}[border_radius_4_measure]" class="form-control sizing" data-name="{$name}[border-bottom-left-radius]">
            <option value=""{if $value.border_radius_4_measure == '' || $value.border_radius_4_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.border_radius_4_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.border_radius_4_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.border_radius_4_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.border_radius_4_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.border_radius_4_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.border_radius_4_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.border_radius_4_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[border-bottom-right-radius]" value="{$value['border-bottom-right-radius']}" class="form-control" placeholder="bottom right" />
          <select name="{$name}[border_radius_3_measure]" class="form-control sizing" data-name="{$name}[border-bottom-right-radius]">
            <option value=""{if $value.border_radius_3_measure == '' || $value.border_radius_3_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.border_radius_3_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.border_radius_3_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.border_radius_3_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.border_radius_3_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.border_radius_3_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.border_radius_3_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.border_radius_3_measure == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      </div>
      {/if}

      {if $styleHide.border.box_shadow !== 1}
      <div class="setting-row">
        <label for="">Box shadow</label>
        <div class="" style="display: inline-block; width: 69%">
          <input type="number" name="{$name}[box_shadow_left]" value="{$value.box_shadow_left}" class="form-control" placeholder="position left" style="margin-bottom: 5px" />
          <select name="{$name}[box_shadow_left_measure]" class="form-control sizing" data-name="{$name}[box_shadow_left]">
            <option value=""{if $value.box_shadow_left_measure == '' || $value.box_shadow_left_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.box_shadow_left_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.box_shadow_left_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.box_shadow_left_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.box_shadow_left_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.box_shadow_left_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.box_shadow_left_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.box_shadow_left_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[box_shadow_top]" value="{$value.box_shadow_top}" class="form-control" placeholder="position top" style="margin-bottom: 5px" />
          <select name="{$name}[box_shadow_top_measure]" class="form-control sizing" data-name="{$name}[box_shadow_top]">
            <option value=""{if $value.box_shadow_top_measure == '' || $value.box_shadow_top_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.box_shadow_top_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.box_shadow_top_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.box_shadow_top_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.box_shadow_top_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.box_shadow_top_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.box_shadow_top_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.box_shadow_top_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[box_shadow_blur]" value="{$value.box_shadow_blur}" class="form-control" placeholder="blur" style="margin-bottom: 5px"/>
          <select name="{$name}[box_shadow_blur_measure]" class="form-control sizing" data-name="{$name}[box_shadow_blur]">
            <option value=""{if $value.box_shadow_blur_measure == '' || $value.box_shadow_blur_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.box_shadow_blur_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.box_shadow_blur_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.box_shadow_blur_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.box_shadow_blur_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.box_shadow_blur_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.box_shadow_blur_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.box_shadow_blur_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <input type="number" name="{$name}[box_shadow_spread]" value="{$value.box_shadow_spread}" class="form-control" placeholder="spread" style="margin-bottom: 5px"/>
          <select name="{$name}[box_shadow_spread_measure]" class="form-control sizing" data-name="{$name}[box_shadow_spread]">
            <option value=""{if $value.box_shadow_spread_measure == '' || $value.box_shadow_spread_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $box_shadow_spread_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.box_shadow_spread_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.box_shadow_spread_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.box_shadow_spread_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.box_shadow_spread_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.box_shadow_spread_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.box_shadow_spread_measure == 'vmax'} selected{/if}>vmax</option>
          </select>

          <div class="colors-inp" style="margin-right: 11px">
            <div id="cp3" class="input-group colorpicker-component">
              <input type="text" name="{$name}[box_shadow_color]" value="{$value.box_shadow_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
              <span class="input-group-addon"><i></i></span>
            </div>
          </div>
          <select name="{$name}[box_shadow_set]" id="" class="form-control" style="width: 100px;">
            <option value=""{if $value.box_shadow_set == ''} selected{/if}>outset</option>
            <option value="inset"{if $value.box_shadow_set == 'inset'} selected{/if}>inset</option>
          </select>
        </div>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size !== 1} active{/if}" id="size-{$id}">

      {if $styleHide.size.width !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_WIDTH}</label>
        <input type="number" name="{$name}[width]" value="{$value.width}" class="form-control" />
        <select name="{$name}[width_measure]" class="form-control sizing" data-name="{$name}[width]">
          <option value=""{if $value.width_measure == '' || $value.width_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.width_measure == 'em'} selected{/if}>em</option>
          <option value="%"{if $value.width_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.width_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.width_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.width_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.width_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.width_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.min_width !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_MIN_WIDTH}</label>
        <input type="number" name="{$name}[min-width]" value="{$value['min-width']}" class="form-control" />
        <select name="{$name}[min_width_measure]" class="form-control sizing" data-name="{$name}[min-width]">
          <option value=""{if $value.min_width_measure == '' || $value.min_width_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.min_width_measure == 'em'} selected{/if}>em</option>
          <option value="%"{if $value.min_width_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.min_width_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.min_width_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.min_width_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.min_width_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.min_width_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.max_width !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXY_MAX_WIDTH}</label>
        <input type="number" name="{$name}[max-width]" value="{$value['max-width']}" class="form-control" />
        <select name="{$name}[max_width_measure]" class="form-control sizing" data-name="{$name}[max-width]">
          <option value=""{if $value.max_width_measure == '' || $value.max_width_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.max_width_measure == 'em'} selected{/if}>em</option>
          <option value="%"{if $value.max_width_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.max_width_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.max_width_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.max_width_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.max_width_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.max_width_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_HEIGHT}</label>
        <input type="number" name="{$name}[height]" value="{$value.height}" class="form-control" />
        <select name="{$name}[height_measure]" class="form-control sizing" data-name="{$name}[height]">
          <option value=""{if $value.height_measure == '' || $value.height_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.height_measure == 'em'} selected{/if}>em</option>
          <option value="%"{if $value.height_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.height_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.height_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.height_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.height_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.height_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.min_height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_MIN_HEIGHT}</label>
        <input type="number" name="{$name}[min-height]" value="{$value['min-height']}" class="form-control" />
        <select name="{$name}[min_height_measure]" id="" class="form-control sizing" data-name="{$name}[min-height]">
          <option value=""{if $value.min_height_measure == '' || $value.min_height_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.min_height_measure == 'em'} selected{/if}>em</option>
          <option value="%"{if $value.min_height_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.min_height_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.min_height_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.min_height_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.min_height_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.min_height_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.max_height !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_MAX_HEIGHT}</label>
        <input type="number" name="{$name}[max-height]" value="{$value['max-height']}" class="form-control" />
        <select name="{$name}[max_height_measure]" id="" class="form-control sizing" data-name="{$name}[max-height]">
          <option value=""{if $value.max_height_measure == '' || $value.max_height_measure == 'px'} selected{/if}>px</option>
          <option value="em"{if $value.max_height_measure == 'em'} selected{/if}>em</option>
          <option value="%"{if $value.max_height_measure == '%'} selected{/if}>%</option>
          <option value="rem"{if $value.max_height_measure == 'rem'} selected{/if}>rem</option>
          <option value="vw"{if $value.max_height_measure == 'vw'} selected{/if}>vw</option>
          <option value="vh"{if $value.max_height_measure == 'vh'} selected{/if}>vh</option>
          <option value="vmin"{if $value.max_height_measure == 'vmin'} selected{/if}>vmin</option>
          <option value="vmax"{if $value.max_height_measure == 'vmax'} selected{/if}>vmax</option>
        </select>
      </div>
      {/if}

    </div>
    <div class="tab-pane{if $styleHide.font === 1 && $styleHide.background === 1 && $styleHide.padding === 1 && $styleHide.border === 1 && $styleHide.size === 1 && $styleHide.display !== 1} active{/if}" id="display-{$id}">

      {if $styleHide.display.float !== 1}
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FLOAT}</label>
        <select name="{$name}[float]" id="" class="form-control">
          <option value=""{if $value.float == ''} selected{/if}></option>
          <option value="none"{if $value.float == 'none'} selected{/if}>{$smarty.const.OPTION_NONE}</option>
          <option value="left"{if $value.float == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $value.float == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.clear !== 1}
      <div class="setting-row">
        <label for="">Clear</label>
        <select name="{$name}[clear]" id="" class="form-control">
          <option value=""{if $value.clear == ''} selected{/if}></option>
          <option value="none"{if $value.clear == 'none'} selected{/if}>{$smarty.const.OPTION_NONE}</option>
          <option value="left"{if $value.clear == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $value.clear == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
          <option value="both"{if $value.clear == 'both'} selected{/if}>{$smarty.const.TEXT_BOTH}</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.display !== 1}
      <div class="setting-row">
        <label for="">Display</label>
        <select name="{$name}[display]" id="" class="form-control">
          <option value=""{if $value.display == ''} selected{/if}></option>
          <option value="block"{if $value.display == 'block'} selected{/if}>Block</option>
          <option value="inline-block"{if $value.display == 'inline-block'} selected{/if}>Inline block</option>
          <option value="inline"{if $value.display == 'inline'} selected{/if}>Inline</option>
          <option value="table"{if $value.display == 'table'} selected{/if}>Table</option>
          <option value="table-row"{if $value.display == 'table-row'} selected{/if}>Table-row</option>
          <option value="table-cell"{if $value.display == 'table-cell'} selected{/if}>Table-cell</option>
          <option value="none"{if $value.display == 'none'} selected{/if}>none</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.position !== 1}
      <div class="setting-row">
        <label for="">Position</label>
        <select name="{$name}[position]" id="" class="form-control">
          <option value=""{if $value.position == ''} selected{/if}></option>
          <option value="relative"{if $value.position == 'relative'} selected{/if}>relative</option>
          <option value="absolute"{if $value.position == 'absolute'} selected{/if}>absolute</option>
          <option value="static"{if $value.position == 'static'} selected{/if}>static</option>
          <option value="fixed"{if $value.position == 'fixed'} selected{/if}>fixed</option>
        </select>
      </div>
      {/if}

      {if $styleHide.display.overflow !== 1}
      <div class="setting-row">
        <label for="">Overflow</label>
        <select name="{$name}[overflow]" id="" class="form-control">
          <option value=""{if $value.overflow == ''} selected{/if}></option>
          <option value="hidden"{if $value.overflow == 'hidden'} selected{/if}>hidden</option>
          <option value="auto"{if $value.overflow == 'auto'} selected{/if}>auto</option>
          <option value="scroll"{if $value.overflow == 'scroll'} selected{/if}>scroll</option>
        </select>
      </div>
      {/if}

      {if $styleHide.size.top !== 1}
        <div class="setting-row">
          <label for="">Top</label>
          <input type="number" name="{$name}[top]" value="{$value.top}" class="form-control" />
          <select name="{$name}[top_measure]" id="" class="form-control sizing" data-name="{$name}[top]">
            <option value=""{if $value.top_measure == '' || $value.top_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.top_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.top_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.top_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.top_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.top_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.top_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.top_measure == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size.left !== 1}
        <div class="setting-row">
          <label for="">Left</label>
          <input type="number" name="{$name}[left]" value="{$value.left}" class="form-control" />
          <select name="{$name}[left_measure]" id="" class="form-control sizing" data-name="{$name}[left]">
            <option value=""{if $value.left_measure == '' || $value.left_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.left_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.left_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.left_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.left_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.left_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.left_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.left_measure == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size.right !== 1}
        <div class="setting-row">
          <label for="">Right</label>
          <input type="number" name="{$name}[right]" value="{$value.right}" class="form-control" />
          <select name="{$name}[right_measure]" id="" class="form-control sizing" data-name="{$name}[right]">
            <option value=""{if $value.right_measure == '' || $value.right_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.right_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.right_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.right_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.right_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.right_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.right_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.right_measure == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size.bottom !== 1}
        <div class="setting-row">
          <label for="">Bottom</label>
          <input type="number" name="{$name}[bottom]" value="{$value.bottom}" class="form-control" />
          <select name="{$name}[bottom_measure]" id="" class="form-control sizing" data-name="{$name}[bottom]">
            <option value=""{if $value.bottom_measure == '' || $value.bottom_measure == 'px'} selected{/if}>px</option>
            <option value="em"{if $value.bottom_measure == 'em'} selected{/if}>em</option>
            <option value="%"{if $value.bottom_measure == '%'} selected{/if}>%</option>
            <option value="rem"{if $value.bottom_measure == 'rem'} selected{/if}>rem</option>
            <option value="vw"{if $value.bottom_measure == 'vw'} selected{/if}>vw</option>
            <option value="vh"{if $value.bottom_measure == 'vh'} selected{/if}>vh</option>
            <option value="vmin"{if $value.bottom_measure == 'vmin'} selected{/if}>vmin</option>
            <option value="vmax"{if $value.bottom_measure == 'vmax'} selected{/if}>vmax</option>
          </select>
        </div>
      {/if}

      {if $styleHide.size['z-index'] !== 1}
        <div class="setting-row">
          <label for="">Z-index</label>
          <input type="number" name="{$name}[z-index]" value="{$value['z-index']}" class="form-control" />
        </div>
      {/if}

      {if $styleHide.size.opacity !== 1}
        <div class="setting-row">
          <label for="">Opacity</label>
          <input type="text" name="{$name}[opacity]" value="{$value.opacity}" class="form-control" />
        </div>
      {/if}

      {if $styleHide.size.rotate !== 1}
        <div class="setting-row">
          <label for="">Rotate</label>
          <input type="number" name="{$name}[rotate]" value="{$value.rotate}" class="form-control" /><span class="px">deg</span>
        </div>
      {/if}

    </div>

  </div>
</div>



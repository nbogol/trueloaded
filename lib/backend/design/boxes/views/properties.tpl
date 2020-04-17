{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.BOX_CATALOG_PROPERTIES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.BOX_CATALOG_PROPERTIES}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">






          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_MANUFACTURER}</label>
            <select name="setting[0][show_manufacturer]" id="" class="form-control">
              <option value=""{if $settings[0].show_manufacturer == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_manufacturer == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_MODEL}</label>
            <select name="setting[0][show_sku]" id="" class="form-control">
              <option value=""{if $settings[0].show_sku == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_sku == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_EAN}</label>
            <select name="setting[0][show_ean]" id="" class="form-control">
              <option value=""{if $settings[0].show_ean == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_ean == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_SHOW_ISBN}</label>
            <select name="setting[0][show_isbn]" id="" class="form-control">
              <option value=""{if $settings[0].show_isbn == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_isbn == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_UPC}</label>
            <select name="setting[0][show_upc]" id="" class="form-control">
              <option value=""{if $settings[0].show_upc == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings[0].show_upc == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>






        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>
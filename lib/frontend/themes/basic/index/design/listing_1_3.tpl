{use class="frontend\design\Info"}
{Info::addBlockToWidgetsList('list-type-1_3')}
<div style="margin-bottom: 50px">
  <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

  <div class="products-listing list-type-1_3 w-list-type-1_3">
    <div class="item" style="width: 100%">
      <div class="image">
        <a><img src="themes/theme-1/img/na.png" alt="ball" title="ball"></a>
        <span class="sale"></span>
      </div>

      <div class="right-area">
        <div class="rating-count">(5)</div>
        <div class="rating">
          <span class="rating-3"></span>
        </div>
        <div class="price">
          <span class="old">£12.00</span>
          <span class="specials">£10.00</span>
        </div>
        <div class="qty-input">
          <label>{output_label const="QTY"}</label>
          <input type="text" name="qty" value="1" class="qty-inp"/>
        </div>

        <div class="buttons">
          <div class="button-wishlist">
            <button type="submit">{$smarty.const.TEXT_WISHLIST_SAVE}</button>
          </div>
          <div class="button-view">
            <a class="view-button">{$smarty.const.VIEW}</a>
          </div>
        </div>
        <div class="compare-box-item">
          <label>
            <span class="cb_title">{$smarty.const.TEXT_SELECT_TO_COMPARE}</span>
            <span class="cb_check"><input type="checkbox" name="compare[]" value="" class="checkbox"><span>&nbsp;</span></span>
          </label>
        </div>
      </div>


      <div class="name">
        <div class="title"><a>{$smarty.const.TEXT_PRODUCT_NAME}</a></div>
        <div class="description"><p>{$smarty.const.TXT_PRODUCT_SHORT_DESCRIPTION}</p></div>
        <div class="products-model"><strong>{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}<span class="colon">:</span></strong> <span>1234</span></div>
        <div class="properties">
          <div class="property">
            <strong>Property 1<span class="colon">:</span></strong>
            <span>34.6</span>
          </div>
          <div class="property">
            <strong>Property 2<span class="colon">:</span></strong>
            <span>10 - 20</span>
          </div>
        </div>
        <div class="attributes">
          <div class="attributes">
            <div>
              <select name="" data-required="Please select">
                <option value="0">Select </option>
              </select>
            </div>
            <div>
              <select name="" data-required="Please select ">
                <option value="0">Select </option>
              </select>
            </div>
          </div>
        </div>

      </div>

      <div class="stock">
        <span class="in-stock"><span class="in-stock-icon">&nbsp;</span>{$smarty.const.TEXT_IN_STOCK}</span>
      </div>

    </div>

  </div>


</div>
<div>
  <div class="demo-heading-3">{$smarty.const.EDIT}</div>

  <div class="edit-list edit-list-type-1_3"{Info::dataClass('.w-list-type-1_3')}>
    <div class="edit-item"{Info::dataClass('.w-list-type-1_3 .item')} style="width: 100%">
      <div class="edit-image"{Info::dataClass('.w-list-type-1_3 .image')}>
        <a{Info::dataClass('.w-list-type-1_3 .image a')}><img src="themes/theme-1/img/na.png" alt="ball" title="ball"></a>
        <span class="edit-sale"{Info::dataClass('.w-list-type-1_3 .sale')}></span>
      </div>

      <div class="edit-right-area"{Info::dataClass('.w-list-type-1_3 .right-area')}>
        <div class="edit-rating-count"{Info::dataClass('.w-list-type-1_3 .rating-count')}>(5)</div>
        <div class="edit-rating"{Info::dataClass('.w-list-type-1_3 .rating')}>
          <span class="rating-3"></span>
        </div>
        <div class="edit-price"{Info::dataClass('.w-list-type-1_3 .price')}>
          <span class="current"{Info::dataClass('.w-list-type-1_3 .price .current')}>£10.00</span>
        </div>
        <div class="edit-price"{Info::dataClass('.w-list-type-1_3 .price')}>
          <span class="old"{Info::dataClass('.w-list-type-1_3 .price .old')}>£12.00</span>
          <span class="specials"{Info::dataClass('.w-list-type-1_3 .price .specials')}>£10.00</span>
        </div>
        <div class="edit-qty-input"{Info::dataClass('.w-list-type-1_3 .qty-input')}>
          <label{Info::dataClass('.w-list-type-1_3 .qty-input label')}>{output_label const="QTY"}</label>
          <span class="edit-qty"{Info::dataClass('.w-list-type-1_3 .qty-inp')}><input type="text" name="qty" value="1" class="qty-inp"/></span>
        </div>
        <div class="edit-buttons"{Info::dataClass('.w-list-type-1_3 .buttons')}>
          <div class="edit-button-wishlist"{Info::dataClass('.w-list-type-1_3 .button-wishlist')}>
            <button type="submit"{Info::dataClass('.w-list-type-1_3 .button-wishlist button')}>{$smarty.const.TEXT_WISHLIST_SAVE}</button>
          </div>
          <div class="edit-button-view"{Info::dataClass('.w-list-type-1_3 .button-view')}>
            <a class="edit-view-button"{Info::dataClass('.w-list-type-1_3 .button-view a')}>{$smarty.const.VIEW}</a>
          </div>
        </div>
        <div class="edit-compare-box-item"{Info::dataClass('.w-list-type-1_3 .compare-box-item')}>
          <label{Info::dataClass('.w-list-type-1_3 .compare-box-item label')}>
            <span class="cb_title">{$smarty.const.TEXT_SELECT_TO_COMPARE}</span>
            <span class="cb_check"><input type="checkbox" name="compare[]" value="" class="checkbox"><span>&nbsp;</span></span>
          </label>
        </div>
      </div>



      <div class="edit-name"{Info::dataClass('.w-list-type-1_3 .name')}>
        <div class="edit-title"{Info::dataClass('.w-list-type-1_3 .title')}><a{Info::dataClass('.w-list-type-1_3 .title a')}>{$smarty.const.TEXT_PRODUCT_NAME}</a></div>
        <div class="edit-description"{Info::dataClass('.w-list-type-1_3 .description')}><p>{$smarty.const.TXT_PRODUCT_SHORT_DESCRIPTION}</p></div>
        <div class="edit-products-model"{Info::dataClass('.w-list-type-1_3 .products-model')}>
          <strong{Info::dataClass('.w-list-type-1_3 .products-model strong')}>{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}<span class="colon">:</span></strong>
          <span{Info::dataClass('.w-list-type-1_3 .products-model > span')}>1234</span>
        </div>
        <div class="edit-properties"{Info::dataClass('.w-list-type-1_3 .properties')}>
          <div class="edit-property"{Info::dataClass('.w-list-type-1_3 .property')}>
            <strong{Info::dataClass('.w-list-type-1_3 .property strong')}>Property 1<span class="colon">:</span></strong>
            <span{Info::dataClass('.w-list-type-1_3 .property > span')}>34.6</span>
          </div>
          <div class="edit-property"{Info::dataClass('.w-list-type-1_3 .property')}>
            <strong{Info::dataClass('.w-list-type-1_3 .property strong')}>Property 2<span class="colon">:</span></strong>
            <span{Info::dataClass('.w-list-type-1_3 .property > span')}>10 - 20</span>
          </div>
        </div>
        <div class="edit-attributes"{Info::dataClass('.w-list-type-1_3 .attributes > attributes')}>
          <div{Info::dataClass('.w-list-type-1_3 .attributes > attributes > div')}>
            <select name="" data-required="Please select">
              <option value="0">Select </option>
            </select>
          </div>
          <div{Info::dataClass('.w-list-type-1_3 .attributes > attributes > div')}>
            <select name="" data-required="Please select ">
              <option value="0">Select </option>
            </select>
          </div>
        </div>

      </div>

      <div class="edit-stock"{Info::dataClass('.w-list-type-1_3 .stock')}>
        <span class="in-stock"><span class="in-stock-icon">&nbsp;</span>Delivery terms label</span>
      </div>


    </div>

  </div>
</div>
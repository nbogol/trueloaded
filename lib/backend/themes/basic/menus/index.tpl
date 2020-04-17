<div class="nestable-lists">
  <div class="tips-switcher"><label for="tips-switcher">{$smarty.const.TEXT_SHOW_TIPS}</label> <input id="tips-switcher" type="checkbox" class="check_on_off"/></div>

  {if $isMultiPlatforms}
      <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
          <ul class="nav nav-tabs">
            {foreach $platforms as $platform}
              <li class="{if $platform['id']==$selected_platform_id} active {/if}"><a class="js_link_platform_menu_select" href="{$platform['link']}" data-platform_id="{$platform['id']}" {if $platform['id']==$selected_platform_id} onclick="return false" {/if}><span>{$platform['text']}</span></a></li>
              {if $platform['id']==$selected_platform_id}{$need_login = $platform.need_login}{/if}
            {/foreach}
          </ul>
      </div>
  {/if}


  <div class="select-list connect-list">

    <div class="menu-list-heading">{$smarty.const.TEXT_AVAILABLE_PAGES}</div>
    <div class="menu-list-search"><input type="text" placeholder="{$smarty.const.TEXT_SEARCH}"/></div>
    <div class="menu-list-buttons">
      <span class="expand-all">{$smarty.const.ENTRY_EXPAND_ALL}</span>
      <span class="collapse-all">{$smarty.const.ENTRY_COLLAPSE_ALL}</span>
    </div>


    <div class="custom-link-wrap">
      <div class="custom-link">
        <ul>
          <li data-type="custom" class="virtual">
            <div class="item-handle"><div class="item-handle-move"></div><span class="temporary">{$smarty.const.ENTRY_CUSTOM_LINK}</span><div class="edit"></div><div class="remove"></div></div>
            <div class="link-settings">

              <div class="tabbable tabbable-custom">
                {if count($languages) > 1}
                <div class="nav nav-tabs">

                  {foreach $languages as $language}
                      <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                  {/foreach}

                </div>
                {/if}
                <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                  {foreach $languages as $language}
                    <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                      <div>
                        <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                      </div>
                        {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                            <br/>
                            <label for="">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</label> 
                            {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderMenu($item.id, $language.id)}
                        {/if}
                    </div>
                  {/foreach}

                </div>
              </div>

              <div class="link-setting">
                <label for="">{$smarty.const.ENTRY_LINK}</label>
                <input type="text" name="link" class="form-control"/>
              </div>
              
                {if $custom_pages|count>0}
              <div class="link-setting">
                <label for="">{$smarty.const.TEXT_CUSTOM_PAGE}</label>
                {\yii\helpers\Html::dropDownList('custom_page', 0, $custom_pages, ['class' => "form-control", 'prompt' => 'Select Page'])}
              </div>
              {/if}
                

              <div class="link-setting">
                <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
              </div>

              <div class="link-setting">
                <label for="">{$smarty.const.TEXT_CLASS}</label>
                <input type="text" name="class" class="form-control"/>
              </div>

              <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

            </div>
            <ul></ul>
          </li>
          <li data-type="all-products" class="virtual">
              <div class="item-handle"><div class="item-handle-move"></div><span class="temporary">{$smarty.const.ENTRY_CUSTOM_CATALOG_LINK}</span><div class="edit"></div><div class="remove"></div></div>
              <div class="link-settings">
                  <div class="tabbable tabbable-custom">
                    {if count($languages) > 1}
                    <div class="nav nav-tabs">

                      {foreach $languages as $language}
                          <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                      {/foreach}

                    </div>
                    {/if}
                    <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                      {foreach $languages as $language}
                        <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                          <div>
                            <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                          </div>
                        </div>
                      {/foreach}

                    </div>
                  </div>
                  
                  <div class="link-setting">
                    <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                  </div>

                  <div class="link-setting">
                    <label for="">{$smarty.const.TEXT_CLASS}</label>
                    <input type="text" name="class" class="form-control"/>
                  </div>

                  <div class="link-setting">
                      {$customFilters}
                  </div>
                  
                  <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
                  
              </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="menu-list-scroll-wrap">
    <div class="menu-list-scroll">


      <div class="type-links">
        <div class="type-links-heading">{$smarty.const.TEXT_CATEGORIES}</div>
        <div class="type-links-content">
            <ul>
              <li data-type-id="999999999" data-type="categories">
                <div class="item-handle"><div class="item-handle-move"></div><div class="searchable">{$smarty.const.TEXT_ALL_CATEGORIES}</div><div class="remove"></div>
                </div>
                <div class="link-settings">

                  <div class="tabbable tabbable-custom">
                      {if count($languages) > 1}
                    <div class="nav nav-tabs">

                      {foreach $languages as $language}
                        <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                      {/foreach}

                    </div>
                      {/if}
                    <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                      {foreach $languages as $language}
                        <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                          <div>
                            <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                          </div>
                        </div>
                      {/foreach}

                    </div>
                  </div>

                  <div class="link-setting">
                    <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                  </div>

                  {if $need_login}
                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="no_logged"/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                    </div>
                  {/if}

                  <div class="link-setting">
                    <label for="">{$smarty.const.TEXT_CLASS}</label>
                    <input type="text" name="class" class="form-control"/>
                  </div>


                  <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                </div>
                <ul></ul>
              </li>
            </ul>
          {function name=categoriesTree}
            {foreach $lst as $categories_id => $item}
              {if (!$checkParent || (isset($item['parent_id']) && $item['parent_id'] == 0))}
                {$childrenCount = (isset($categories[$categories_id]['children']) && count($categories[$categories_id]['children'])>0)}
                <li data-type-id="{$categories_id}" data-type="categories" class="{if $childrenCount}cat-parent closed{/if}" id="cat_{$categories_id}">
                  <div class="item-handle"><div class="item-handle-move"></div><div class="item-close {if $smarty.const.MENU_CATEGORIES_COLLAPSED !== false && $childrenCount}closed{/if}" data-close-id="cat_{$categories_id}"></div><div class="searchable">{$item.categories_name}</div><div class="edit"></div><div class="remove"></div>
                    <div class="link-setting-c">
                      <label for="">{$smarty.const.TEXT_SHOW_SUBCATEGORIES}: </label> <input type="checkbox" name="sub_categories" class="check_on_off" checked/>
                    </div>
                  </div>
                  <div class="link-settings">

                    <div class="tabbable tabbable-custom">
                        {if count($languages) > 1}
                      <div class="nav nav-tabs">

                        {foreach $languages as $language}
                          <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                        {/foreach}

                      </div>
                        {/if}
                      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                        {foreach $languages as $language}
                          <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                            <div>
                              <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                            </div>
                          </div>
                        {/foreach}

                      </div>
                    </div>

                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="sub_categories"/> <label for="">{$smarty.const.TEXT_SHOW_SUBCATEGORIES}</label>
                    </div>

                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                    </div>

                    {if $need_login}
                      <div class="link-setting">
                        <input type="checkbox" class="uniform" name="no_logged"/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                      </div>
                    {/if}

                    <div class="link-setting">
                      <label for="">{$smarty.const.TEXT_CLASS}</label>
                      <input type="text" name="class" class="form-control"/>
                    </div>


                    <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                  </div>
                    {if $childrenCount>0}
                  <ul {if $smarty.const.MENU_CATEGORIES_COLLAPSED !== false}style="display:none"{/if}>
                    {call categoriesTree lst=$categories[$categories_id]['children'] checkParent=false}
                  </ul>
                    {/if}
                </li>
                {/if}
            {/foreach}
          {/function}

          <ul class="categories-tree">
            {call categoriesTree lst=$categories checkParent=true}
          </ul>
        </div>
      </div>


      <div class="type-links">
        <div class="type-links-heading">{$smarty.const.TEXT_BRANDS}</div>
        <div class="type-links-content">
          <ul>
            <li data-type-id="999999998" data-type="brands">
              <div class="item-handle"><div class="item-handle-move"></div><div class="searchable">{$smarty.const.ALL_BRANDS}</div><div class="remove"></div>
              </div>
              <div class="link-settings">

                <div class="tabbable tabbable-custom">
                  {if count($languages) > 1}
                    <div class="nav nav-tabs">

                      {foreach $languages as $language}
                        <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                      {/foreach}

                    </div>
                  {/if}
                  <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                    {foreach $languages as $language}
                      <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                        <div>
                          <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                        </div>
                      </div>
                    {/foreach}

                  </div>
                </div>

                <div class="link-setting">
                  <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                </div>

                {if $need_login}
                  <div class="link-setting">
                    <input type="checkbox" class="uniform" name="no_logged"/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                  </div>
                {/if}

                <div class="link-setting">
                  <label for="">{$smarty.const.TEXT_CLASS}</label>
                  <input type="text" name="class" class="form-control"/>
                </div>


                <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

              </div>
              <ul></ul>
            </li>
          </ul>

          <ul class="brands-tree">
            {foreach $brands as $item}
                <li data-type-id="{$item.manufacturers_id}" data-type="brands">
                  <div class="item-handle"><div class="item-handle-move"></div><div class="item-close"></div><div class="searchable">{$item.manufacturers_name}</div><div class="edit"></div><div class="remove"></div>

                  </div>
                  <div class="link-settings">

                    <div class="tabbable tabbable-custom">
                      {if count($languages) > 1}
                        <div class="nav nav-tabs">

                          {foreach $languages as $language}
                            <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                          {/foreach}

                        </div>
                      {/if}
                      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                        {foreach $languages as $language}
                          <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                            <div>
                              <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                            </div>
                          </div>
                        {/foreach}

                      </div>
                    </div>

                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                    </div>

                    {if $need_login}
                      <div class="link-setting">
                        <input type="checkbox" class="uniform" name="no_logged"/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                      </div>
                    {/if}

                    <div class="link-setting">
                      <label for="">{$smarty.const.TEXT_CLASS}</label>
                      <input type="text" name="class" class="form-control"/>
                    </div>


                    <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                  </div>
                </li>
            {/foreach}
          </ul>

        </div>
      </div>
          
      <div class="type-links">
        <div class="type-links-heading">{$smarty.const.TEXT_DEFAULT_PAGES}</div>
        <div class="type-links-content">
          <ul>
            {foreach $default_pages as $page}
              <li data-type-id="{$page.type_id}" data-type="default">
                <div class="item-handle"><div class="item-handle-move"></div><div class="searchable">{$page.name}</div><div class="edit"></div><div class="remove"></div></div>
                <div class="link-settings">

                  <div class="tabbable tabbable-custom">
                      {if count($languages) > 1}
                    <div class="nav nav-tabs">
                      {foreach $languages as $language}
                        <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                      {/foreach}
                    </div>
                    {/if}
                    <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                      {foreach $languages as $language}
                        <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                          <div>
                            <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                          </div>
                          {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                            <br/>
                            <label for="">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</label> 
                            {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderMenu($item.id, $language.id)}
                          {/if}
                        </div>
                      {/foreach}
                    </div>
                  </div>

                  <div class="link-setting">
                    <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                  </div>

                  {if $page.opt_need_login && $need_login}
                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="no_logged"/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                    </div>
                  {/if}

                  <div class="link-setting">
                    <label for="">{$smarty.const.TEXT_CLASS}</label>
                    <input type="text" name="class" class="form-control"/>
                  </div>


                  <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                </div>
              </li>
            {/foreach}

          </ul>
        </div>
      </div>
          
      <div class="type-links">
        <div class="type-links-heading">{$smarty.const.TEXT_INFO_PAGES}</div>
        <div class="type-links-content">
          <ul>
            {foreach $info as $info_item}
              {if isset($info_item.title)}
                <li data-type-id="{$info_item.information_id}" data-type="info">
                  <div class="item-handle"><div class="item-handle-move"></div><div class="searchable">{$info_item.title}</div><div class="edit"></div><div class="remove"></div></div>
                  <div class="link-settings">

                    <div class="tabbable tabbable-custom">
                        {if count($languages) > 1}
                      <div class="nav nav-tabs">

                        {foreach $languages as $language}
                          <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                        {/foreach}

                      </div>
                        {/if}
                      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                        {foreach $languages as $language}
                          <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                            <div>
                              <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                            </div>
                          </div>
                        {/foreach}

                      </div>
                    </div>

                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                    </div>

                    {if $need_login}
                      <div class="link-setting">
                        <input type="checkbox" class="uniform" name="no_logged"/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                      </div>
                    {/if}

                    <div class="link-setting">
                      <label for="">{$smarty.const.TEXT_CLASS}</label>
                      <input type="text" name="class" class="form-control"/>
                    </div>


                    <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                  </div>
                </li>
              {/if}
            {/foreach}
          </ul>
        </div>
      </div>

      <div class="type-links">
        <div class="type-links-heading">Account pages</div>
        <div class="type-links-content">
          <ul>
              {foreach $account_pages as $page}
                <li data-type-id="{$page.type_id}" data-type="account">
                  <div class="item-handle"><div class="item-handle-move"></div><div class="searchable">{$page.name}</div><div class="edit"></div><div class="remove"></div></div>
                  <div class="link-settings">

                    <div class="tabbable tabbable-custom">
                        {if count($languages) > 1}
                          <div class="nav nav-tabs">
                              {foreach $languages as $language}
                                <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                              {/foreach}
                          </div>
                        {/if}
                      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                          {foreach $languages as $language}
                            <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$language.id}" data-language="{$language.id}">
                              <div>
                                <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="" class="form-control" />
                              </div>
                                {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                                  <br/>
                                  <label for="">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</label>
                                    {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderMenu($item.id, $language.id)}
                                {/if}
                            </div>
                          {/foreach}
                      </div>
                    </div>

                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="target"/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                    </div>

                    <div class="link-setting">
                      <label for="">{$smarty.const.TEXT_CLASS}</label>
                      <input type="text" name="class" class="form-control"/>
                    </div>


                    <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                  </div>
                </li>
              {/foreach}

          </ul>
        </div>
      </div>

    </div>
    </div>
  </div>


  <div class="menu-right-list">

    <div class="select-menu">
      <div class="menu-list-heading">{$smarty.const.TEXT_MENU}</div>
      <form id="select-menu" action="{$action_url_select_menu}" method="get">
        {assign var="url" value=\yii\helpers\ArrayHelper::map($platforms, 'id', 'platform_url')}
        <input type="hidden" name="platform_id" value="{$selected_platform_id}" data-href="{$url[$selected_platform_id]}">
        <select name="menu" class="form-control" data-id="{$current_menu.id}">
          {foreach $menus as $item}
            <option value="{$item.id}"{if $item.id == $current_menu.id} selected{/if}>{$item.menu_name}</option>
          {/foreach}
        </select>
        <div class="menu-name">{$current_menu.menu_name}</div>
        <span class="edit-name">
          <input type="text" value="{$current_menu.menu_name}" id="txt_menu_name" class="form-control" placeholder="{$smarty.const.TEXT_ENTER_NEW_MENU}"/>
          <span class="btn btn-primary btn-save-menu-name">{$smarty.const.IMAGE_SAVE}</span>
          <span class="btn btn-cancel btn-cancel-menu-name">{$smarty.const.IMAGE_CANCEL}</span>
        </span>
        {if $current_menu.menu_name == ''}

        {/if}
        {if $current_menu.menu_name != '' && $current_menu.menu_name != 'Categories' && $current_menu.menu_name != 'Header menu' && $current_menu.menu_name != 'Account box'}
        <div class="edit"></div>
        <div class="remove"></div>
        {/if}
      </form>
    </div>


    <div class="drop-list connect-list">


      {function name=menuTree}
          {foreach $menu as $item}
            {if $item.parent_id == $parent}
              <li{if isset($item.id)} data-id="{$item.id}"{/if} data-type-id="{$item.link_id}" data-type="{$item.link_type}" {if isset($item.new_category) || isset($item.new_brand)} class="new-category"{/if}>
                <div class="item-handle"><div class="item-handle-move"></div><div class="item-close"></div>

                  {$item.name}

                  {if $item.shown}
                    <span class="shown">{$smarty.const.TEXT_SHOW_AS} "{$item.shown}"</span>
                  {/if}

                  <div class="remove"></div>

                  {if $item.link_id != 999999999}
                    <div class="edit"></div>
                  {/if}
                  {if $item.link_type == 'categories' && $item.link_id != 999999999 && $item.link_id != 8888887 && $item.link_id != 8888888 && $item.link_id != 8888884 && $item.link_id != 8888883 && $item.link_id != 8888882 && $item.link_id != 8888879 && $item.link_id != 8888878 && $item.link_id != 8888877}
                    <div class="link-setting-c">
                      <label for="">{$smarty.const.TEXT_SHOW_SUBCATEGORIES}: </label> <input type="checkbox" name="sub_categories" class="check_on_off" {if $item.sub_categories == '1'}checked{/if}/>
                    </div>
                  {/if}

                </div>
                <div class="link-settings">

            {*if $item.link_type != 'default'*}
                  <div class="tabbable tabbable-custom">
                      {if count($languages) > 1}
                    <div class="nav nav-tabs">

                      {foreach $languages as $language}
                        <div{if $language.id == $languages_id} class="active"{/if}><a href="#{$item.id}_{$language.id}" data-toggle="tab">{$language.logo}<span>{$language.name}</span></a></div>
                      {/foreach}

                    </div>
                      {/if}
                    <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">

                      {foreach $languages as $language}
                        <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">
                          <div class="current-name">{$smarty.const.TEXT_CURRENT_NAME}
                            {if isset($item.titles[$language.id])}
                              "{$item.titles[$language.id]}"
                            {else}
                              "{$item.name}"
                            {/if}
                            </div>
                          <div>
                            <label for="">{$smarty.const.TEXT_TITLE}</label> <input type="text" name="title" value="{$item.titles[$language.id]}" class="form-control" />
                          </div>
                          {if ($item.link_type == 'default' || $item.link_type == 'custom') && \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                            <br/>
                            <label for="">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</label> 
                            {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderMenu($item.id, $language.id)}
                          {/if}
                        </div>
                      {/foreach}

                    </div>
                  </div>
            {*/if*}

                  {if $item.link_type == 'custom'}
                  <div class="link-setting">
                    <label for="">{$smarty.const.ENTRY_LINK}</label>
                    <input type="text" name="link" value="{$item.link}" class="form-control"/>
                  </div>
                  
                  {if $custom_pages|count>0}
                  <div class="link-setting">
                    <label for="">{$smarty.const.TEXT_CUSTOM_PAGE}</label>  
                    {\yii\helpers\Html::dropDownList('custom_page', $item.theme_page_id, $custom_pages, ['class' => "form-control", 'prompt' => 'Select Page'])}
                  </div>
                  {/if}
                 {/if}

                  <div class="link-setting">
                    <input type="checkbox" class="uniform" name="target" {if $item.target_blank == '1'}checked{/if}/> <label for="">{$smarty.const.TEXT_OPEN_NEW_TAB}</label>
                  </div>

                  {if $need_login && $item.link_type != 'custom'}
                    <div class="link-setting">
                      <input type="checkbox" class="uniform" name="no_logged" {if $item.no_logged == '1'}checked{/if}/> <label for="">{$smarty.const.SHOW_FOR_NON_LOGGED}</label>
                    </div>
                  {/if}

                  <div class="link-setting">
                    <label for="">{$smarty.const.TEXT_CLASS}</label>
                    <input type="text" name="class" value="{$item.class}" class="form-control"/>
                  </div>
                  
                  <div class="link-setting">
                      {$item.customFilters}
                  </div>

                  <div class="item-buttons"><span class="btn btn-primary btn-apply">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>

                </div>
                <ul>
                  {call menuTree parent=$item.id}
                </ul>
              </li>
            {/if}
          {/foreach}
      {/function}

      <ul>
      {call menuTree parent=0}
      </ul>

    </div>

  </div>


  <div class="btn-bar">
    <div class="btn-left">
      <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
    <div class="btn-right">
      <span class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>


</div>
<script>

  {if $new_categories > 0}
    setTimeout(function(){
      alertMessage('<div class="confirm"><p>{$smarty.const.ADDED_CATEGORIES}</p><p style="font-size: 14px">{$smarty.const.YOUR_NEED_SAVE_MENU}</p><p><span class="btn btn-cancel">{$smarty.const.SEE_CHANGES}</span></p></div>');
    }, 200);
  {/if}

  {if $new_brands > 0}
    setTimeout(function(){
      alertMessage('<div class="confirm"><p>{$smarty.const.ADDED_BRANDS}</p><p style="font-size: 14px">{$smarty.const.YOUR_NEED_SAVE_MENU_BRANDS}</p><p><span class="btn btn-cancel">{$smarty.const.SEE_CHANGES}</span></p></div>');
    }, 200);
  {/if}

  $.fn.tips = function(text){
    return this.each(function() {
      $(this).hover(function(e){
        if ($.cookie('tips') == 1){
          $('body').append('<div class="tips-wrap"><div class="tips">' + text + '</div></div>');
          $('.tips-wrap').css({ left: e.pageX, top: e.pageY + 20 })
        }
      }, function(){
        $('.tips-wrap').remove()
      })
    })
  };

  var getQueryString = function () {
    var query_string = { };
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
      var pair = vars[i].split("=");
      var arr;
      if (typeof query_string[pair[0]] === "undefined") {
        query_string[pair[0]] = decodeURIComponent(pair[1]);
      } else if (typeof query_string[pair[0]] === "string") {
        arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
        query_string[pair[0]] = arr;
      } else {
        query_string[pair[0]].push(decodeURIComponent(pair[1]));
      }
    }
    return query_string;
  }();

   var addQueryString = function(param, val){
    getQueryString[param] = val;
    var url = window.location.origin + window.location.pathname;
    var count = 0;
    $.each(getQueryString, function(key, value){
      if (typeof value != "undefined" && key != '') {
        if (count == 0) url = url + '?';
        else url = url + '&';
        url = url + key + '=' + value;
        count++
      }
    });
    window.history.replaceState({ }, '', url);

  };

  $(document).ready(function() {

    var categories_tree_length = $('.categories-tree > li').length;

    var brands_tree_length = $('.brands-tree > li').length;

    var changed = false;

    var item_editing = { };

    function menuListButtons(){
      var type_links_heading_closed = $('.type-links-heading.closed');

      $('.menu-list-buttons span').removeClass('active');
      if (type_links_heading_closed.length == 0){
        $('.menu-list-buttons .expand-all').addClass('active')
      }
      if (type_links_heading_closed.length == $('.type-links-heading').length){
        $('.menu-list-buttons .collapse-all').addClass('active')
      }
    }
    menuListButtons();

    $('.create_item').off('click').on('click', function(){
      alertMessage('<div class="confirm"><p>{$smarty.const.TEXT_TYPE_NAME_MENU|replace:"'":"\'"}</p><p><input type="text" class="form-control"/></p><p><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL|replace:"'":"\'"}</span> <span class="btn btn-primary btn-yes">{$smarty.const.IMAGE_SAVE|replace:"'":"\'"}</span></p></div>');

      $('.confirm .btn-yes').on('click', function(){

        var n = $('.confirm input').val();
        $('.pop-up-content').html('<div class="preloader"></div>');
        $.post('{$action_url_save_menu}', {
          menu_id: 0,
          menu_name: n
        }, function(d){
          $('.pop-up-content').html('<div class="response">' + d[0] + '</div>');
          addQueryString('menu', d[1]);
          setTimeout(function(){ $('.popup-box-wrap').remove() }, 500);
          $.get(select_menu.attr('action'), { menu: d[1] }, function(data){
            $('.content-container').html(data)
          });
        }, 'json')

      });

      return false;
    });


    var select_menu = $('#select-menu');

    $('.btn-cancel-menu-name').on('click', function(){
      $('input', select_menu).val($('.menu-name', select_menu).text());
      $('.edit', select_menu).trigger('click');
    });
    $('.btn-save-menu-name').on('click', function(){
      $.get('{tep_href_link('menus/save-name')}', {
        name: $('input[id="txt_menu_name"]', select_menu).val(),
        id: $('select', select_menu).val()
      }, function(){
        $('.edit-name', select_menu).hide();
        $('.menu-name', select_menu).show();
        $('.edit', select_menu).removeClass('active');
        $('.menu-name', select_menu).text($('input[id="txt_menu_name"]', select_menu).val());
        $('select option:selected', select_menu).text($('input[id="txt_menu_name"]', select_menu).val())
      }, 'json')
    });

    var confirmSaveChangedMenu = function (target_get){
      alertMessage('<div class="confirm"><p>{$smarty.const.TEXT_MENU_CHANGED|replace:"'":"\'"}</p><p>{$smarty.const.TEXT_DO_YOU_WONT_SAVE_THIS_MENU|replace:"'":"\'"}</p><p><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL|replace:"'":"\'"}</span> <span class="btn btn-no">{$smarty.const.TEXT_BTN_NO|replace:"'":"\'"}</span> <span class="btn btn-primary btn-yes">{$smarty.const.TEXT_BTN_YES|replace:"'":"\'"}</span></p></div>');

      $('.confirm .btn-cancel').on('click', function(){
        $('.select-menu select').val($('.select-menu select').data('id'));
        $('.popup-box-wrap').remove()
      });
      $('.confirm .btn-no').on('click', function(){
        $('.popup-box-wrap').remove();
        $(target_get.param).each(function(){
          addQueryString(this.name,this.value);
        });
        $.get(target_get.url, target_get.param, function(d){
          $('.content-container').html(d)
        });
      });
      $('.confirm .btn-yes').on('click', function(){
        $.post('{$action_url_save_menu}', {
          menu_id: $('.select-menu select').data('id'),
          menu_name: $('.select-menu input[id="txt_menu_name"]').val(),
          list: menuList()
        }, function(d){
          if (typeof d == "object"){
            $('.pop-up-content').html('<div class="response">' + d[0] + '</div>');
          } else {
            $('.pop-up-content').html('<div class="response">' + d + '</div>');
          }
          $(target_get.param).each(function(){
            addQueryString(this.name,this.value);
          });
          $.get(target_get.url, target_get.param, function(d){
            $('.content-container').html(d)
          });
        }, 'json');
      });
    };

    var onLeavePage = function(url){
      var new_page_url = url || {
        url:'',
        params:[]
      };
      if ( !new_page_url.url ) {
        new_page_url.url = select_menu.attr('action');
        new_page_url.params = select_menu.serializeArray();
      }

      if (JSON.stringify( start_list ) != JSON.stringify( menuList() )){
        confirmSaveChangedMenu(new_page_url);
      } else {
        $(new_page_url.params).each(function(){
          addQueryString(this.name,this.value);
        });
        $.get(new_page_url.url, new_page_url.params, function(d){
          $('.content-container').html(d)
        });
      }

      return false
    };

    $('select', select_menu).on('change', onLeavePage);
    $('.js_link_platform_menu_select').on('click', function(){
      var param = {
        url: select_menu.attr('action'),//$(this).attr('href'),
        params:[]
      };
      param.params.push({
        name:'platform_id',
        value:$(this).attr('data-platform_id')
      });
      param.params.push({
        name:'menu',
        value:$('select', select_menu).val()
      });
      onLeavePage(param);
      return false;
    });

    $('.select-menu .edit').on('click', function(){
      if ($(this).hasClass('active')){
        $('.select-menu .edit-name').hide();
        $('.select-menu .menu-name').show();
        $(this).removeClass('active')
      } else {
        $('.select-menu .edit-name').show();
        $('.select-menu .menu-name').hide();
        $(this).addClass('active')
      }
    });

    $('.select-menu .remove').on('click', function(){
      $('.select-menu input[id="txt_menu_name"]').val('');
      $('.btn-save').trigger('click')
    });

    $('.type-links-heading').on('click', function(){
      if ($(this).hasClass('closed')){
        $(this).removeClass('closed')
      } else {
        $(this).addClass('closed')
      }

      var lh = '';
      $('.type-links-heading').each(function(){
        if ($(this).hasClass('closed')){
          lh = lh + '0'
        }else{
          lh = lh + '1'
        }
      });
      addQueryString('lh', lh);


      menuListButtons()
    });

    if (typeof getQueryString['lh'] != "undefined") {
      $('.type-links-heading').each(function (i) {
        if (getQueryString['lh'].substring(i, i + 1) == '0') {
          $(this).addClass('closed')
        }
      })
    }

    $('.menu-list-scroll').on('scroll', function(){
      addQueryString('sc', $(this).scrollTop());
    });

    if (typeof getQueryString['sc'] != "undefined") {
      $('.menu-list-scroll').scrollTop(getQueryString['sc'])
    }

    $('.menu-list-buttons .expand-all').on('click', function(){
      $('.type-links-heading').removeClass('closed');
      menuListButtons()
    });

    $('.menu-list-buttons .collapse-all').on('click', function(){
      $('.type-links-heading').addClass('closed');
      menuListButtons()
    });

    $('.menu-list-search input').on('keyup', function(){
      var search = $(this).val();
      var searchable = $('.searchable');
      searchable.parents('li').hide();

      addQueryString('s', $(this).val());

      searchable.each(function(){
        var html = '';
        var text = $(this).text();
        var re = new RegExp(search, 'i');
        if (text.search(re) != -1){
          var rep = new RegExp('(' + search + ')', 'i');
          html = text.replace(rep, "<b>$1</b>");
          $(this).html(html);
          $(this).parents('li').show().closest('ul').prev().show();
        } else {
          $(this).html(text);
        }

      })
    });

    if (typeof getQueryString['s'] != "undefined") {
      $('.menu-list-search input').val(getQueryString['s']).trigger('keyup')
    }

    var left_height = function(){
      setTimeout(function(){
        var menu_right_list = $('.menu-right-list').height();
        var select_list_height = $('.select-list').height();
        var menu_list_scroll_wrap = $('.menu-list-scroll-wrap');

          var d = menu_right_list - select_list_height;
          if (menu_list_scroll_wrap.height() + d > 500){
            $('.menu-list-scroll').css('max-height', menu_list_scroll_wrap.height() + d - 2)
          } else {
            $('.menu-list-scroll').css('max-height', 500)
          }

      }, 500)
    };
    left_height();
    $(window).on('change_menu', left_height);

    var categories_switch = function(){
      $('.drop-list .link-setting-c').each(function(){
        if ($(this).closest('li').find('li').length > 0){
          $(this).show()
        } else {
          $(this).hide()
        }
      })
    };
    categories_switch();
    $(window).on('change_menu', categories_switch);

    function bSwitch(obj){
      $(".check_on_off", obj).bootstrapSwitch({
        onInit: function (element, arguments) {
          if (arguments == true){
            $(this).closest('li').find('> ul').show(200)
          } else {
            $(this).closest('li').find('> ul').hide(200)
          }

          return true;
        },
        onSwitchChange: function (element, arguments) {
          if (arguments == true){
            $(this).closest('li').find('> ul').show(200)
          } else {
            $(this).closest('li').find('> ul').hide(200)
          }

          return true;
        },
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
      })
    };
    bSwitch($('.drop-list'));


    var list = [];

    var sort = {
      connectWith: ".drop-list ul",
      tolerance: 'pointer',
      handle: '.item-handle-move',
      update: function(d, e){

        function apply(item){
          if ($('> ul', item).length == 0){
            item.append('<ul></ul>');
          }
          if (item.data('type') == 'categories'){
            item.find('ul').sortable().sortable( "destroy" );
          }
          item.find('ul').sortable(sort).sortable({
            receive: function(e,ui) {
              copyHelper= null;
            }
          });

          bSwitch(item);

          if (item.data('type') == 'custom' && item.data('type-id') == undefined){
            item.attr('data-type-id', Math.floor(Math.random() * 1000000))
          }
          var id_i = [];
          var rnd = Math.floor(Math.random() * 1000000);
          item.find('.nav-tabs a').each(function(i){
            id_i[i] = $(this).attr('href').replace("#", rnd + '_');
            $(this).attr('href', '#' + id_i[i]);
          });
          
          if (!id_i.length){
            id_i.push(rnd + '_{$languages_id}');
          }
          
          item.find('.tab-pane').each(function(i){
            $(this).attr('id', id_i[i]);
            {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                addURL(id_i[i]);
            {/if}
          });
        }

        if (e.item.data('type-id') == '999999999'){
          {if !$smarty.const.MENU_CATEGORIES_COLLAPSED}
            $('> ul', e.item).html($('.categories-tree').html());
          ///$('> ul', e.item).html($($('.categories-tree').html()).remove('> ul'));
          {else}
          $('> ul', e.item).html($('.categories-tree').html());
          {/if}
        } else if (e.item.data('type-id') == '999999998') {
          $('> ul', e.item).html($('.brands-tree').html());
        }
        apply(e.item);


        $(window).trigger('change_menu');

      }
    };

    $( ".drop-list ul" ).sortable(sort);
    $( ".select-list ul" ).sortable({
      connectWith: ".connect-list ul",
      tolerance: 'pointer',
      handle: '.item-handle-move',
      forcePlaceholderSize: false,
      helper: function(e,li) {
        copyHelper= li.clone().insertAfter(li);
        return li.clone();
      },
      stop: function() {
        copyHelper && copyHelper.remove();
      },
      update: function( event, ui){
        if (ui.item.closest('div').attr('class') == 'type-links-content'){
          return false;
        }
      }
    });
    $(".drop-list ul").sortable({
      handle: '.item-handle-move',
      receive: function(e,ui) {
        copyHelper= null;
      }
    });

    var drop_list = $('.drop-list');
    drop_list.on('click', '.item-handle .remove', function(){
      $(this).closest('li').remove();
      $(window).trigger('change_menu');
    });
    drop_list.on('click', '.item-handle .edit', function(){
      $('.link-settings').slideUp(300);
      var this_li = $(this).closest('li');
      this_li.find('> .link-settings:hidden').slideDown(300);
      if (this_li.hasClass('active')){
        $('.ui-sortable li').removeClass('active');
        addQueryString('edit');
      } else {

        var titles = [];
        $('> .link-settings .tab-pane', this_li).each(function(i){
          titles[i] = {
            language_id: $(this).data('language'),
            title: $('input[name="title"]', this).val()
          };
        });
        var target_blank = 0;
        if ($('> .link-settings input[name="target"]:checked', this_li).length > 0){
          target_blank = 1;
        }
        var no_logged = 0;
        if ($('> .link-settings input[name="no_logged"]:checked', this_li).length > 0){
          no_logged = 1;
        }
        item_editing = {
          link: $('> .link-settings input[name="link"]', this_li).val(),
          target_blank: target_blank,
          no_logged: no_logged,
          'class': $('> .link-settings input[name="class"]', this_li).val(),
          titles: titles
        };
        $('.ui-sortable li').removeClass('active');
        this_li.addClass('active');
        addQueryString('edit', this_li.data('id'));
      }

      $(window).trigger('change_menu');
    });
    drop_list.on('click', '.link-settings .btn-cancel', function(){
      var this_li = $(this).closest('li');
      $('> .link-settings input[name="link"]', this_li).val(item_editing.link);
      $('> .link-settings input[name="target"]', this_li).prop( "checked", item_editing.target_blank );
      $('> .link-settings input[name="no_logged"]', this_li).prop( "checked", item_editing.no_logged );
      $('> .link-settings input[name="class"]', this_li).val(item_editing.class);
      $('> .link-settings .tab-pane', this_li).each(function(i){
        $('input[name="title"]', this).val(item_editing.titles[i].title)
      });

      $(this).closest('li').find('.item-handle .edit').trigger('click')
    });
    drop_list.on('click', '.link-settings .btn-apply', function(){
      $(this).closest('li').find('.item-handle .edit').trigger('click')
    });

    $('.nav-tabs a').on('click', function(){
      $(this).closest('.nav-tabs').find('> div').removeClass('active');
      $(this).parent().addClass('active')
    });


    if (typeof getQueryString['edit'] != "undefined") {
      $('.drop-list li[data-id="' + getQueryString['edit'] + '"] > .item-handle .edit').trigger('click')
    }


    var menuList = function(){
      list = [];
      $('.drop-list li').each(function(i){
          if ($(this).closest('form').attr('name') === 'filters') return true;

        var _this = $(this);
        var link = $('> .link-settings input[name="link"]', _this).val();
        var target_blank = 0;
        if ($('> .link-settings input[name="target"]:checked', _this).length > 0){
          target_blank = 1;
        }
        var no_logged = 0;
        if ($('> .link-settings input[name="no_logged"]:checked', _this).length > 0){
          no_logged = 1;
        }
        var sub_categories = 0;
        if ($('> .item-handle input[name="sub_categories"]:checked', _this).length > 0){
          sub_categories = 1;
        }
        
        var custom_categories = '';
        if ($("form[name='filters']", _this).length > 0){
            custom_categories = $("form[name='filters']", _this).serialize();
        }

        var titles = [];
        $('> .link-settings .tab-pane', _this).each(function(i){
          titles.push({
            language_id: $(this).data('language'),
            title: $('input[name="title"]', this).val()
          });
        });
        {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
          var custom = [];
          custom = getOldUrls(_this);
        {/if}        
        
        var custom_page = 0;
        if ($('select[name=custom_page]', _this).val()){
            custom_page = $('select[name=custom_page]', _this).val();
        }

        list.push({
          id: _this.data('id'),
          type: _this.data('type'),
          type_id: _this.data('type-id'),
          link: link,
          target_blank: target_blank,
          no_logged: no_logged,
          'class': $('> .link-settings input[name="class"]', _this).val(),
          sub_categories: sub_categories,
          custom_categories: custom_categories,
          titles: titles,
          {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
          custom: custom,
          {/if}
          custom_page: custom_page,
          parent: {
            id: _this.parent().parent().data('id'),
            type: _this.parent().parent().data('type'),
            type_id: _this.parent().parent().data('type-id')
          }
        });
      });

        {if $smarty.const.MENU_DATA_LIKE_ONE_INPUT == 'True'}
        return JSON.stringify(list);
        {else}
        return list;
        {/if}
    };

    var start_list = menuList();

    $('.btn-bar .btn-save').on('click', function(){
      alertMessage('<div class="preloader"></div>');


      $.post('{$action_url_save_menu}', {
        menu_id: $('.select-menu select').val(),
        menu_name: $('.select-menu input[id="txt_menu_name"]').val(),
        list: menuList()
      }, function(d){
        if (typeof d == "object"){
          $('.pop-up-content').html('<div class="response">' + d[0] + '</div>');
          $.get($('.select-menu form').attr('action'), { menu: d[1] }, function(d){
            $('.content-container').html(d);
            setTimeout(function(){ $('.popup-box-wrap').remove() }, 500)
          })
        } else if (d == 'Deleted'){
          $('.pop-up-content').html('<div class="response">' + d + '</div>');
          addQueryString('menu', 0);
          $.get($('.select-menu form').attr('action'), { }, function(d){
            $('.content-container').html(d);
            setTimeout(function(){ $('.popup-box-wrap').remove() }, 500)
          })
        } else {
          $('.pop-up-content').html('<div class="response">' + d + '</div>');
          addQueryString('menu', $('select', select_menu).val());
          $.get($('.select-menu form').attr('action'), { menu: $('.select-menu select').val() }, function(d){
            $('.content-container').html(d);
            setTimeout(function(){ $('.popup-box-wrap').remove() }, 500)
          })
        }
      }, 'json')
    });


    $('.btn-bar .btn-cancel').on('click', function(){
      $.get($('.select-menu form').attr('action'), { }, function(d){
        $('.content-container').html(d);
        addQueryString('menu');
      })
    });


    var tips_switcher = $("#tips-switcher");
    if ($.cookie('tips') == undefined || $.cookie('tips') == 1){
      $.cookie('tips', 1, { expires: 365 });
      tips_switcher.prop( "checked", true );
    } else {
      $.cookie('tips', 0, { expires: 365 });
      tips_switcher.prop( "checked", false );
    }
    tips_switcher.bootstrapSwitch({
      onSwitchChange: function (element, arguments) {
        if (arguments == true){
          $.cookie('tips', 1, { expires: 365 })
        } else {
          $.cookie('tips', 0)
        }

        return true;
      },
      onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
      handleWidth: '20px',
      labelWidth: '24px'
    });


    $('.select-list .item-handle').tips('{$smarty.const.TEXT_YOU_CAN_DRAG_DROP|replace:"'":"\'"}');
    $('.select-menu select').tips('{$smarty.const.TEXT_YOU_CAN_CHOOSE|replace:"'":"\'"}');
    $('.create_item').tips('{$smarty.const.TEXT_PRESS_ADD_NEW_MENU|replace:"'":"\'"}')

  });

$(function(){
  $('.categories-tree li, .drop-list li').each(function(){
    $(this).parent().closest('li').find('> .item-handle .item-close').show()
  });


  $('.item-close').on('click', function(){
    if (typeof($(this).attr('data-close-id')) != 'undefined') {
      id = $(this).attr('data-close-id');
      $('#' + id + ' > ul').toggle();
      $(this).toggleClass('closed');
    } else {
      if ($(this).hasClass('closed')){
        $(this).removeClass('closed');
        $(this).closest('li').find('> ul').show()
      } else {
        $(this).addClass('closed');
        $(this).closest('li').find('> ul').hide()
      }
    }
  })
})


</script>
{use class="\yii\helpers\Html"}
{\backend\assets\BDPAsset::register($this)|void}
{if !empty($holder)}
{assign var=holder value=" "|cat:$holder}
{else}
{assign var=holder value=""}
{/if}
<div class="wl-td">
<label>{$smarty.const.TITLE_MONTH}/{$smarty.const.TITLE_YEAR}</label>
{Html::input('text', 'month_year', $month_year, ['class' =>'form-control', 'placeholder' => TEXT_SELECT|cat:$holder])}
</div>

<script>
    var checkSelection = function(){
        //check custom    
        return true;
    }
    
    $(document).ready(function(){
        
        $('input[name=month_year]').datepicker({ 
            'minViewMode':1,
            'format':'mm/yyyy',
            autoclose:true,
            });
            
    })
    
</script>
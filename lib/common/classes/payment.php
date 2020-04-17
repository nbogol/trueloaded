<?php

/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

class payment extends modules\ModuleCollection {

    public $modules;
    public $selected_module;
    protected $include_modules = [];
    private $manager;

// class constructor
    function __construct($module = '', \common\services\OrderManager $manager)
    {
        global $cart;
// EOF: WebMakers.com Added: Downloads Controller
        $module_key = 'MODULE_PAYMENT_INSTALLED';
        $this->manager = $manager;
        if (tep_not_null(constant($module_key))) {
            $this->modules = explode(';', constant($module_key));
            /*foreach ($this->modules as &$class) {
                $class = basename(str_replace('\\', '/', $class));
            }
            unset($class);*/
            $include_modules = array();
            if ((tep_not_null($module)) && (in_array($module . '.php', $this->modules))) {
                $this->selected_module = $module;
                $include_modules[] = array('class' => $module, 'file' => $module . '.php');
            } elseif ((tep_not_null($module)) && (in_array(substr($module, 0, strpos($module, '_')) . '.php'/* . substr($PHP_SELF, (strrpos($PHP_SELF, '.') + 1))*/, $this->modules))) {
                $this->selected_module = substr($module, 0, strpos($module, '_'));
                $include_modules[] = array('class' => substr($module, 0, strpos($module, '_')), 'file' => substr($module, 0, strpos($module, '_')) . '.php'/* . substr($PHP_SELF, (strrpos($PHP_SELF, '.') + 1))*/);
            } else {
                if (defined('MODULE_PAYMENT_FREECHARGER_STATUS') && MODULE_PAYMENT_FREECHARGER_STATUS == 1 && ($cart->show_total() == 0 and $cart->show_weight == 0)) {
                    $this->selected_module = $module;
                    $include_modules[] = array('class' => 'freecharger', 'file' => 'freecharger.php');
                } else {
                    // All Other Payment Modules
                    if (is_array($this->modules)) {
                        foreach ($this->modules as $value) {
                            $class = substr($value, 0, strrpos($value, '.'));
                            // Don't show Free Payment Module
                            if ($class != 'freecharger') {
                                $include_modules[] = array('class' => $class, 'file' => $value);
                            }
                        }
                    }
// EOF: WebMakers.com Added: Downloads Controller
                }
            }
            \common\helpers\Translation::init('payment');
            $builder = new \common\classes\modules\ModuleBuilder($manager);
            foreach ($include_modules as $include_module) {
                $class = $include_module['class'];
                $module = "\\common\\modules\\orderPayment\\{$class}";
                if (!class_exists($module)) {
                    continue;
                }
                $this->include_modules[$class] = $builder(['class' => $module]);
            }
        }
    }

// class methods
    /* The following method is needed in the checkout_confirmation.php page
      due to a chicken and egg problem with the payment class and order class.
      The payment modules needs the order destination data for the dynamic status
      feature, and the order class needs the payment module title.
      The following method is a work-around to implementing the method in all
      payment modules available which would break the modules in the contributions
      section. This should be looked into again post 2.2.
     */
    public function update_status() {
        if (is_array($this->include_modules)) {
            if (is_object($this->include_modules[$this->selected_module])) {
                if (function_exists('method_exists')) {
                    if (method_exists($this->include_modules[$this->selected_module], 'update_status')) {
                        $this->include_modules[$this->selected_module]->update_status();
                    }
                }
            }
        }
    }

    public function isPaymentSelected() {
        return isset($this->include_modules[$this->selected_module]) && is_object($this->include_modules[$this->selected_module]) && $this->include_modules[$this->selected_module]->enabled;
    }

    public function getSelectedPayment() {
        if ($this->isPaymentSelected()){
            return $this->include_modules[$this->selected_module];
        }
        return false;
    }

    public function isPaymentEnabled($payment_class){
        return isset($this->getEnabledModules()[$payment_class]) && $this->getEnabledModules()[$payment_class]->enabled;
    }

    public function getPaymentUrl(){
        if ($_selected = $this->getSelectedPayment()){
            if (property_exists($_selected, 'form_action_url')){
                return $_selected->form_action_url;
            }
        }
        return null;
    }

    public function javascript_validation() {
        $js = '';
        if (is_array($this->include_modules)) {
            $modules = $this->getEnabledModules();
            foreach ($modules as $_payment) {
                $js .= $_payment->javascript_validation();
            }
            if (count($modules)){
                $js .= "\n" . '  if (payment_value == null && submitter != 1) {' . "\n" . // ICW CREDIT CLASS Gift Voucher System
                    '    error_message = error_message + ' . json_encode(JS_ERROR_NO_PAYMENT_MODULE_SELECTED, JSON_PARTIAL_OUTPUT_ON_ERROR) . ';' . "\n" .
                    '    error = 1;' . "\n" .
                    '  }' . "\n\n";
            }
            if (defined('GERMAN_SITE') && GERMAN_SITE == 'True' && !defined('ONE_PAGE_POST_PAYMENT')) {
                $js .= ' if (!document.one_page_checkout.conditions.checked){' . "\n" .
                        '   error = 1;' . "\n" .
                        '   error_message = error_message + ' . json_encode(ERROR_JS_CONDITIONS_NOT_ACCEPTED, JSON_PARTIAL_OUTPUT_ON_ERROR) . ';' . "\n" .
                        ' }' . "\n";
            }
            $JS_ERROR = JS_ERROR;
            return <<<EOD
            <script language="javascript">
function check_form() {
    var error = 0;
    var error_message = "{$JS_ERROR}";
    var payment_value = null;
    if (document.one_page_checkout.payment.length) {
        for (var i=0; i<document.one_page_checkout.payment.length; i++) {
            if (document.one_page_checkout.payment[i].checked) {
                payment_value = document.one_page_checkout.payment[i].value;
            }
        }
    } else if (document.one_page_checkout.payment.checked) {
        payment_value = document.one_page_checkout.payment.value;
    } else if (document.one_page_checkout.payment.value) {
        payment_value = document.one_page_checkout.payment.value;
    }
    {$js}
    if (error == 1 && submitter != 1) {
        alert(error_message);
        return false;
    } else {
        return true;
    }
}

</script>
EOD;
        }

        return $js;
    }

    public function getEnabledModules() {
        static $enabled = null;
        if (is_null($enabled)){
            $enabled = [];
            foreach ($this->include_modules as $class => $module){
                if (is_object($module) && $module->enabled && $module->getVisibily($this->manager->getModulesVisibility())){
                    $enabled[$class] = $module;
                }
            }
        }
        return $enabled;
    }

    private $selectionMode = false;
    public function selection($opc = false, $onlyOnline = false, $visibility = ['shop_order', 'shop_quote', 'shop_sample', 'admin', 'pos'], $groups_id = 0) {
        $selection_array = [];
        if (!is_array($visibility)) {
            $visibility = [$visibility];
        }
        $onlyOffline = false;
        if (defined('GROUPS_IS_SHOW_PRICE') && GROUPS_IS_SHOW_PRICE == false) {
            if (in_array('shop_order', $visibility)) {
                $onlyOnline = false;
                $onlyOffline = true;
            }
        }
        if (is_array($this->include_modules)) {
            $haveSubscription = $this->manager->getOrderInstance()->haveSubscription();
            /** @var \common\extensions\CustomerModules\CustomerModules $CustomerModules */
            $CustomerModules = \common\helpers\Acl::checkExtension('CustomerModules', 'checkAvailable');
            foreach ($this->include_modules as $class => $_payment) {
                if (is_object($_payment) && $_payment->enabled && $_payment->getVisibily($visibility)) {//only enabled
                    if (!$_payment->getGroupVisibily(\common\classes\platform::currentId(), $groups_id)) {
                        continue;
                    }

                    if ($CustomerModules && !\Yii::$app->user->isGuest) {
                      if (!$CustomerModules::checkAvailable(\common\classes\platform::currentId(), \Yii::$app->user->getId(), $class)) {
                        continue;
                      }
                    }

                    if ($haveSubscription) {
                        if (!$_payment->haveSubscription()) {
                            continue;
                        }
                    }
                    if ($onlyOnline && !$_payment->isOnline()) {
                        continue;
                    }
                    if ($onlyOffline && $_payment->isOnline()) {
                        continue;
                    }
                    if ($opc) {
                        $selection = $_payment->selection();
                        if (is_array($selection)) {
                            $selection['module_status'] = $_payment->enabled;
                            $selection_array[] = $selection;
                        }
                    } else {
                        $selection = $_payment->selection();
                        if (is_array($selection)){
                            $selection_array[] = $selection;
                        }
                    }
                }
            }
            $this->selectionMode = true;
        }
        $this->registerCallbacks();
        
        return $selection_array;
    }
    
    private $callbacks = [];
    /**
     * Register JSCallback of payments
     */
    public function registerCallbacks(){
        if ($this->hasCallbacks()){
            \Yii::$app->getView()->registerJsFile(\frontend\design\Info::themeFile('/js/payment.js'));
            \Yii::$app->getView()->registerJs($this->addCallbacksToCheckout());
        }
    }
    
    public function hasCallback($code){
        return in_array($code, array_keys($this->callbacks));
    }
    /*
     * Register JSCallback function name of particular payment
     */
    public function registerCallback($code, $callback){
        $this->callbacks[$code] = $callback;
    }
    
    public function getCallbacks(){
        return $this->callbacks;
    }
    
    public function hasCallbacks(){
        return count($this->callbacks);
    }
    
    protected function makeReplacement($callback, &$jsData){
        if (empty($callback)) return;
        if (is_array($jsData)){
            foreach($jsData as $_key => &$data){
                $this->makeReplacement($callback, $data);
            }
        } elseif (is_string($jsData)) {
            $hasParams = strpos($callback ,"(");
            $params = '';
            if ($hasParams !== false){
                $params = substr($callback, $hasParams);
                $callback = substr($callback, 0, $hasParams);
                $params = preg_replace("/\(.*\)/", "", $params);
            }
            $jsData = preg_replace("/function[\s]{1,3}{$callback}/", "window.$callback = function" . $params, $jsData);
        }
    }
    
    protected function globaliseCallback(string $callback){
        $view = \Yii::$app->getView();
        if (property_exists($view, 'js') && is_array($view->js)){
            foreach($view->js as &$jsData){
                $this->makeReplacement($callback, $jsData);
            }
        }
    }
    
    protected function addCallbacksToCheckout() :string {
        $js = "";
        if ($this->hasCallbacks()){
            $callbacks = $this->getCallbacks();
            foreach($callbacks as $pCode => $cValue){
                if (is_array($cValue)) {
                    //to do
                } else if (is_string($cValue)){
                    $this->globaliseCallback($cValue);
                }
            }
            $callbacks = json_encode($callbacks);
            $formName = (!$this->selectionMode ? 'frmCheckoutConfirm' : 'frmCheckout');
            $js .= <<<EOD
paymentCollection.init(document.getElementById('{$formName}'));
paymentCollection.setCallbacks({$callbacks});
EOD;
            if (!$this->selectionMode){
                $js .= <<<EOD
paymentCollection.setNeedConfirmation(false);
EOD;
            }
        }
        return $js;
    }
  
    protected function isWithoutConfirmation(): bool
    {
        return defined('SKIP_CHECKOUT') && SKIP_CHECKOUT === 'True';
    }

    //ICW CREDIT CLASS Gift Voucher System
    // check credit covers was setup to test whether credit covers is set in other parts of the code
    public function check_credit_covers() {
        return $this->manager->get('credit_covers');
    }

    public function pre_confirmation_check() {
        if ($this->isPaymentSelected()){
            if ($this->manager->get('credit_covers')) { //  ICW CREDIT CLASS Gift Voucher System
                $this->include_modules[$this->selected_module]->enabled = false; //ICW CREDIT CLASS Gift Voucher System
                $this->include_modules[$this->selected_module] = NULL; //ICW CREDIT CLASS Gift Voucher System
                $payment_modules = ''; //ICW CREDIT CLASS Gift Voucher System
            } else { //ICW CREDIT CLASS Gift Voucher System
                $this->include_modules[$this->selected_module]->pre_confirmation_check();
            }
        }
    }

//ICW CREDIT CLASS Gift Voucher System

    public function confirmation() {
        if ($this->isPaymentSelected()){
            $confirmation = $this->include_modules[$this->selected_module]->confirmation();
            $this->registerCallbacks();
            return $confirmation;
        }
    }
    
    public function isOnline() {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->isOnline();
        }
    }

    public function process_button() {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->process_button();
        }
    }

    public function before_process() {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->before_process();
        }
    }

    public function before_subscription($id) {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->before_subscription($id);
        }
    }

    public function get_subscription_info($id) {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->get_subscription_info($id);
        }
    }

    public function after_process() {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->after_process();
        }
    }

    public function checkout_initialization_method() {
        $initialize_array = array();

        foreach($this->getEnabledModules() as $_payment){
            if (method_exists($_payment, 'checkout_initialization_method')) {
                $sort_order = (int) $_payment->sort_order;
                if (isset($initialize_array[$sort_order])) {
                    if ($sort_order == 0) {
                        $inc = 1000; // put not ordered to the end
                    } else {
                        $inc = 1;
                    }
                    $sort_order = max(array_keys($initialize_array)) + $inc;
                }
                $initialize_array[$sort_order] = $_payment->checkout_initialization_method();
            }
        }

        ksort($initialize_array, SORT_NATURAL); //NUMERIC
        return $initialize_array;
    }

    public function showPaynowButton($type = 0) {
        $initialize_array = array();

        foreach($this->getEnabledModules() as $_payment){
            if (method_exists($_payment, 'checkButtonOnProduct') && $_payment->checkButtonOnProduct() && method_exists($_payment, 'checkout_initialization_method')) {
                $sort_order = (int) $_payment->sort_order;
                if (isset($initialize_array[$sort_order])) {
                    if ($sort_order == 0) {
                        $inc = 1000; // put not ordered to the end
                    } else {
                        $inc = 1;
                    }
                    $sort_order = max(array_keys($initialize_array)) + $inc;
                }
                $initialize_array[$sort_order] = $_payment->checkout_initialization_method(1, $type);
            }
        }

        ksort($initialize_array, SORT_NATURAL); //NUMERIC
        return $initialize_array;
    }

    /**
     * get payment modules which support express checkout button at product form.
     * @return array of payment module codes
     */
    public function getExpressPayments() {
        $initialize_array = array();

        foreach($this->getEnabledModules() as $class => $_payment){
            if (method_exists($_payment, 'checkButtonOnProduct') && $_payment->checkButtonOnProduct() && method_exists($_payment, 'checkout_initialization_method')) {
                $initialize_array[] = $class;
            }
        }

        return $initialize_array;
    }

    public function get($class, $all = false){
        return ($all ? $this->include_modules[$class] ?? false :$this->getEnabledModules()[$class] ?? false) ;
    }

    public function get_error() {
        if ($this->isPaymentSelected()){
            $messageStack = \Yii::$container->get('message_stack');
            $_error = $this->include_modules[$this->selected_module]->get_error();
            if (is_object($messageStack) && method_exists($messageStack, 'save_to_base')) {
                if (isset($_error['title']) && isset($_error['error']))
                    $messageStack->save_to_base('payment', $_error['error'], 'error', $_error['title']);
            }
            return $_error;
        }
    }

    public static function module($module, $front = false) {
        $file = $front ? DIR_WS_MODULES . 'shipping/' . $module . '.php' : DIR_FS_DOCUMENT_ROOT . '/includes/modules/shipping/' . $module . '.php';
        if (!is_null($module) && file_exists($file)) {
            if (is_file(DIR_FS_CATALOG . DIR_WS_LANGUAGES . 'modules/shipping/' . $module . '.php')) {
                include_once( DIR_FS_CATALOG . DIR_WS_LANGUAGES . 'modules/shipping/' . $module . '.php' );
            }
            include_once( $file);
            if (class_exists($module)) {
                return new $module;
            }
        }
        return null;
    }

    public function getConfirmationTitle(){
        if ($module = $this->getSelectedPayment()){
            if (method_exists($module, 'getTitle')){
                return $module->getTitle($this->manager->getPayment());
            }
        }
        return '';
    }

    public function getTransactionalModules() {
        static $transactional = null;
        if (is_null($transactional)){
            $transactional = [];
            foreach ($this->include_modules as $class => $module){
                if (is_object($module) && $module instanceof \common\classes\modules\TransactionalInterface){
                    $transactional[$class] = $module;
                }
            }
        }
        return $transactional;
    }
    
    public function getTransactionSearchModules() {
        static $transactionSearch = null;
        if (is_null($transactionSearch)){
            $transactionSearch = [];
            foreach ($this->include_modules as $class => $module){
                if (is_object($module) && $module instanceof \common\classes\modules\TransactionSearchInterface){
                    $transactionSearch[$class] = $module;
                }
            }
        }
        return $transactionSearch;
    }
    
    public function confirmationCurlAllowed() {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->confirmationCurlAllowed();
        }
    }

    public function confirmationAutosubmit() {
        if ($this->isPaymentSelected() && method_exists($this->include_modules[$this->selected_module], 'confirmationAutosubmit')) {
            return $this->include_modules[$this->selected_module]->confirmationAutosubmit();
        }
    }

    public function popUpMode() {
        if ($this->isPaymentSelected()) {
            return $this->include_modules[$this->selected_module]->popUpMode();
        }
    }

    public function processButton() {

        if ($this->isPaymentSelected() && method_exists($this->include_modules[$this->selected_module], 'processButton')) {
            return $this->include_modules[$this->selected_module]->processButton();
        }
        return false;
    }

}

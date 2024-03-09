<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular;

use verfriemelt\wrapped\_\DateTime\DateTime;
use verfriemelt\wrapped\_\Exception\Input\InputException;
use verfriemelt\wrapped\_\Formular\FormTypes\Button;
use verfriemelt\wrapped\_\Formular\FormTypes\Checkbox;
use verfriemelt\wrapped\_\Formular\FormTypes\Date;
use verfriemelt\wrapped\_\Formular\FormTypes\FormType;
use verfriemelt\wrapped\_\Formular\FormTypes\Hidden;
use verfriemelt\wrapped\_\Formular\FormTypes\Password;
use verfriemelt\wrapped\_\Formular\FormTypes\Select;
use verfriemelt\wrapped\_\Formular\FormTypes\Text;
use verfriemelt\wrapped\_\Formular\FormTypes\Textarea;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Input\CSRF;
use verfriemelt\wrapped\_\Input\Filter;
use verfriemelt\wrapped\_\Output\Viewable;
use verfriemelt\wrapped\_\Session\Session;
use verfriemelt\wrapped\_\Template\Template;
use Override;

class Formular implements Viewable
{
    final public const string METHOD_POST = 'POST';

    final public const string METHOD_GET = 'GET';

    final public const string CSRF_FIELD_NAME = '_csrf';

    final public const string FORM_FIELD_NAME = '_form';

    private ?Filter $filter = null;

    private array $elements = [];

    private string $method = self::METHOD_POST;

    private string $cssClass = '';

    private string $cssId = '';

    private string $action;

    private readonly string $formname;

    private readonly string $csrfTokenName;

    private bool $storeValuesOnFail = false;

    private bool $prefilledWithSubmitData = false;

    private readonly Session $session;

    private Template $tpl;

    protected Request $request;

    private function generateCSRF()
    {
        $csrf = new CSRF($this->session);
        return $csrf->generateToken($this->csrfTokenName);
    }

    public function __construct(
        string $name,
        Request $request,
        Session $session,
        ?Filter $filter = null,
        ?Template $template = null
    ) {
        $this->formname = $name;
        $this->filter = $filter;
        $this->csrfTokenName = 'csrf-' . md5($this->formname);

        $this->session = $session;

        $this->addHidden(self::CSRF_FIELD_NAME, $this->generateCSRF());
        $this->addHidden(self::FORM_FIELD_NAME, $this->formname);

        if (!$template) {
            $this->tpl = new Template();
            $this->tpl->render(__DIR__ . '/Template/Formular.tpl.php');
        } else {
            $this->tpl = $template;
        }

        $this->request = $request;
        $this->action = $request->uri();
    }

    public function setCssClass($cssClass): Formular
    {
        $this->cssClass = $cssClass;
        return $this;
    }

    public function setCssId($id): Formular
    {
        $this->cssId = $id;
        return $this;
    }

    public function action($path): Formular
    {
        $this->action = $path;
        return $this;
    }

    public function addText($name, $value = null): Text
    {
        $input = new Text($name);
        $input->setValue($value);
        $input->setFilterItem($this->filter->request()->has($name));

        $this->elements[$name] = $input;

        return $input;
    }

    public function addDate($name, ?DateTime $value = null): Date
    {
        $input = new Date($name);

        if ($value) {
            $input->setValue($value);
        }

        $input->setFilterItem($this->filter->request()->has($name));

        $this->elements[$name] = $input;

        return $input;
    }

    public function addPassword($name, $value = null)
    {
        $input = new Password($name);
        $input->setValue($value);
        $input->setFilterItem($this->filter->request()->has($name));

        $this->elements[$name] = $input;

        return $input;
    }

    public function addHidden($name, $value = null): Hidden
    {
        $filter = $this->filter->request()->has($name);

        if ($value !== null) {
            $filter->allowedValues([$value]);
        }

        $input = new Hidden($name);
        $input->setValue($value);
        $input->setFilterItem($filter);

        $this->elements[$name] = $input;

        return $input;
    }

    public function addButton($name, $value = null)
    {
        $button = new Button($name);
        $button->setValue($value);
        $this->elements[$name] = $button;

        $button->setFilterItem($this->filter->request()->has($name));

        return $button;
    }

    public function addCheckbox($name, $value = null)
    {
        $checkbox = new Checkbox($name, $value);
        $this->elements[$name] = $checkbox;

        $checkbox->setFilterItem($this->filter->request()->has($name));

        return $checkbox;
    }

    public function addSelect($name, $value = null)
    {
        $select = new Select($name, $value);
        $select->setFilterItem($this->filter->request()->has($name));

        $this->elements[$name] = $select;

        return $select;
    }

    public function addTextarea($name, $value = null)
    {
        $input = new Textarea($name);
        $input->setValue($value);
        $input->setFilterItem($this->filter->request()->has($name));

        $this->elements[$name] = $input;

        return $input;
    }

    public function addSubmit($value)
    {
        return $this->addButton('submit', $value)->type('submit')->setOptional();
    }

    public function storeValuesOnFail($bool = true): Formular
    {
        $this->storeValuesOnFail = $bool;
        return $this;
    }

    /**
     * switches between post and get method
     *
     * @param type $method
     */
    public function setMethod($method): Formular
    {
        if (!in_array($method, [self::METHOD_GET, self::METHOD_POST])) {
            return false;
        }

        $this->method = $method;
        return $this;
    }

    public function isPosted(): bool
    {
        return $this->request->request()->get(self::FORM_FIELD_NAME) === $this->formname;
    }

    /**
     * @return mixed sended form values
     */
    public function get(string $name)
    {
        $input = ($this->method === self::METHOD_POST) ?
            $this->request->request() :
            $this->request->query();

        if (!isset($this->elements[$name])) {
            return null;
        }

        return $this->elements[$name]->parseValue($input->get($name, null));
    }

    private function preFillFormWithSendData()
    {
        foreach ($this->elements as $element) {
            // skip csrf token, otherwise the form will silently fail
            if (in_array($this->elements, [self::CSRF_FIELD_NAME, self::FORM_FIELD_NAME])) {
                continue;
            }

            if ($element instanceof Password) {
                continue;
            }

            $data = $this->get($element->name);

            if (is_string($data) || is_bool($data)) {
                $element->setValue($this->get($element->name));
            }
        }

        return $this;
    }

    /**
     * checks if form has been sent and all filter criteria are met
     */
    public function hasValidated(): bool
    {
        $validated = false;

        if (
            (
                $this->method === self::METHOD_POST
                && $this->request->requestMethod() === 'POST'
                && $this->get(self::FORM_FIELD_NAME) === $this->formname
            ) || $this->method === self::METHOD_GET) {
            $failed = false;

            foreach ($this->elements as $element) {
                try {
                    $element->getFilterItem()->validate();
                } catch (InputException) {
                    $failed = true;
                    $element->addCssClass('input-error');
                }
            }

            if ($this->storeValuesOnFail && $failed) {
                $this->prefilledWithSubmitData = true;
                $this->preFillFormWithSendData();
            }

            $this->addHidden(self::CSRF_FIELD_NAME, $this->generateCSRF());

            $validated = !$failed;
        }

        return $validated;
    }

    /**
     * manually mark field as errorprone
     *
     * @throws Exception
     */
    public function markFailed(string $fieldName): Formular
    {
        if (!isset($this->elements[$fieldName])) {
            throw new \Exception("illegal form element {$fieldName}");
        }

        $this->elements[$fieldName]->addCssClass('input-error');

        if (!$this->prefilledWithSubmitData) {
            $this->prefilledWithSubmitData = true;
            $this->preFillFormWithSendData();
        }

        return $this;
    }

    #[Override]
    public function getContents(): string
    {
        $r = $this->tpl->createRepeater('elements');
        $this->tpl->set('method', $this->method);
        $this->tpl->set('action', $this->action);
        $this->tpl->set('cssClass', $this->cssClass);
        $this->tpl->set('cssId', $this->cssId);

        foreach ($this->elements as $element) {
            $r->set('element', $element->fetchHtml());
            $r->save();
        }

        return $this->tpl->run();
    }

    public function fetchElement(string $name): ?FormType
    {
        return $this->elements[$name] ?? null;
    }
}

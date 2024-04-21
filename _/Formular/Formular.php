<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular;

use Override;
use RuntimeException;
use verfriemelt\wrapped\_\DateTime\DateTime;
use verfriemelt\wrapped\_\DI\Container;
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
use verfriemelt\wrapped\_\Http\Request\RequestStack;
use verfriemelt\wrapped\_\Input\CSRF;
use verfriemelt\wrapped\_\Input\Filter;
use verfriemelt\wrapped\_\Output\Viewable;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;
use Exception;

class Formular implements Viewable
{
    final public const string METHOD_POST = 'POST';

    final public const string METHOD_GET = 'GET';

    final public const string CSRF_FIELD_NAME = '_csrf';

    final public const string FORM_FIELD_NAME = '_form';

    private array $elements = [];

    private string $method = self::METHOD_POST;

    private string $cssClass = '';

    private string $cssId = '';

    private string $action;

    private readonly string $csrfTokenName;

    private bool $storeValuesOnFail = false;

    private bool $prefilledWithSubmitData = false;

    public function __construct(
        private readonly string $formname,
        protected RequestStack $requestStack,
        private readonly ?Filter $filter = null,
        private readonly Template $tpl = new Template(new TemplateRenderer(new Container())),
    ) {
        $template = \file_get_contents(__DIR__ . '/Template/Formular.tpl.php');
        if (!is_string($template)) {
            throw new RuntimeException('cannot load template');
        }

        $this->tpl->parse($template);

        $this->action = $this->requestStack->getCurrentRequest()->uri();

        $this->csrfTokenName = 'csrf-' . \md5($this->formname);

        $this->addHidden(self::CSRF_FIELD_NAME, $this->generateCSRF());
        $this->addHidden(self::FORM_FIELD_NAME, $this->formname);
    }

    private function generateCSRF(): string
    {
        $csrf = new CSRF($this->requestStack->getCurrentRequest());
        return $csrf->generateToken($this->csrfTokenName);
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

    public function addSubmit($value): Button
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
     */
    public function setMethod(string $method): Formular
    {
        if (!in_array($method, [self::METHOD_GET, self::METHOD_POST], true)) {
            throw new RuntimeException("cannot handle method: {$method}");
        }

        $this->method = $method;
        return $this;
    }

    public function isPosted(): bool
    {
        return $this->requestStack->getCurrentRequest()->request()->get(self::FORM_FIELD_NAME) === $this->formname;
    }

    /**
     * @return mixed sended form values
     */
    public function get(string $name): mixed
    {
        $input = ($this->method === self::METHOD_POST) ?
            $this->requestStack->getCurrentRequest()->request() :
            $this->requestStack->getCurrentRequest()->query();

        if ($input->hasNot($name)) {
            return null;
        }

        return $this->elements[$name]->parseValue($input->get($name));
    }

    private function preFillFormWithSendData(): static
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
                && $this->requestStack->getCurrentRequest()->requestMethod() === 'POST'
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
            throw new Exception("illegal form element {$fieldName}");
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

        return $this->tpl->render();
    }

    public function fetchElement(string $name): ?FormType
    {
        return $this->elements[$name] ?? null;
    }
}

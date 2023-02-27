<?php

namespace Orvital\Extensions\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Taken from https://github.com/korridor/laravel-model-validation-rules
 */
class ExistsEloquent implements ValidationRule
{
    /**
     * Class name of model.
     *
     * @var class-string<Model>
     */
    private string $model;

    /**
     * Relevant key in the model.
     */
    private ?string $key;

    /**
     * Closure that can extend the eloquent builder.
     */
    private ?Closure $builderClosure;

    /**
     * Custom validation message.
     */
    private ?string $customMessage = null;

    /**
     * Custom translation key for message.
     */
    private ?string $customMessageTranslationKey = null;

    /**
     * Create a new rule instance.
     *
     * @param  class-string<Model>  $model  Class name of model
     * @param  string|null  $key  Relevant key in the model
     * @param  Closure|null  $builderClosure  Closure that can extend the eloquent builder
     */
    public function __construct(string $model, ?string $key = null, ?Closure $builderClosure = null)
    {
        $this->model = $model;
        $this->key = $key;
        $this->setBuilderClosure($builderClosure);
    }

    /**
     * Set a custom validation message.
     *
     * @return $this
     */
    public function withMessage(string $message): self
    {
        $this->customMessage = $message;

        return $this;
    }

    /**
     * Set a translated custom validation message.
     *
     * @return $this
     */
    public function withCustomTranslation(string $translationKey): self
    {
        $this->customMessageTranslationKey = $translationKey;

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Model|Builder $builder */
        $builder = new $this->model();
        $modelKeyName = $builder->getKeyName();

        if (null === $this->key) {
            $builder = $builder->where($modelKeyName, $value);
        } else {
            $builder = $builder->where($this->key, $value);
        }
        
        if (null !== $this->builderClosure) {
            $builderClosure = $this->builderClosure;
            $builder = $builderClosure($builder);
        }

        if ($builder->doesntExist()) {
            if ($this->customMessage !== null) {
                $fail($this->customMessage);
            } else {
                $fail($this->customMessageTranslationKey ?? 'extensions::validation.exists_model')->translate([
                    'attribute' => $attribute,
                    'model' => strtolower(class_basename($this->model)),
                    'value' => $value,
                ]);
            }
        }
    }

    public function setBuilderClosure(?Closure $builderClosure): void
    {
        $this->builderClosure = $builderClosure;
    }

    /**
     * @return $this
     */
    public function query(Closure $builderClosure): self
    {
        $this->setBuilderClosure($builderClosure);

        return $this;
    }
}

<?php
// core/Validator.php

namespace Core;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $messages = [
        'required' => 'O campo :field é obrigatório.',
        'email' => 'O campo :field deve ser um e-mail válido.',
        'min' => 'O campo :field deve ter no mínimo :param caracteres.',
        'max' => 'O campo :field deve ter no máximo :param caracteres.',
        'numeric' => 'O campo :field deve ser numérico.',
        'alpha' => 'O campo :field deve conter apenas letras.',
        'alphaNum' => 'O campo :field deve conter apenas letras e números.',
        'confirmed' => 'A confirmação de :field não confere.',
        'unique' => 'Este :field já está em uso.',
        'exists' => 'O :field selecionado é inválido.',
        'url' => 'O campo :field deve ser uma URL válida.',
        'date' => 'O campo :field deve ser uma data válida.',
        'image' => 'O campo :field deve ser uma imagem.',
    ];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($ruleList as $rule) {
                $this->validateRule($field, $value, $rule);
            }
        }
        
        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validateRule(string $field, $value, string $rule): void
    {
        $params = [];
        
        if (str_contains($rule, ':')) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        $method = 'validate' . ucfirst($rule);
        
        if (method_exists($this, $method)) {
            if (!$this->$method($field, $value, $params)) {
                $this->addError($field, $rule, $params);
            }
        }
    }

    private function addError(string $field, string $rule, array $params = []): void
    {
        $message = $this->messages[$rule] ?? "O campo :field é inválido.";
        $message = str_replace(':field', $field, $message);
        $message = str_replace(':param', $params[0] ?? '', $message);
        
        $this->errors[$field][] = $message;
    }

    private function validateRequired(string $field, $value, array $params): bool
    {
        if (is_null($value)) return false;
        if (is_string($value) && trim($value) === '') return false;
        if (is_array($value) && count($value) === 0) return false;
        return true;
    }

    private function validateEmail(string $field, $value, array $params): bool
    {
        return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(string $field, $value, array $params): bool
    {
        $min = (int) ($params[0] ?? 0);
        return empty($value) || strlen($value) >= $min;
    }

    private function validateMax(string $field, $value, array $params): bool
    {
        $max = (int) ($params[0] ?? 0);
        return empty($value) || strlen($value) <= $max;
    }

    private function validateConfirmed(string $field, $value, array $params): bool
    {
        $confirmField = $field . '_confirmation';
        return $value === ($this->data[$confirmField] ?? null);
    }

    private function validateNumeric(string $field, $value, array $params): bool
    {
        return empty($value) || is_numeric($value);
    }

    private function validateUrl(string $field, $value, array $params): bool
    {
        return empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateUnique(string $field, $value, array $params): bool
    {
        if (empty($value)) return true;
        
        $table = $params[0] ?? $field . 's';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;
        
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $bindings = ['value' => $value];
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $bindings['except_id'] = $exceptId;
        }
        
        $count = $db->fetchColumn($sql, $bindings);
        
        return $count == 0;
    }

    private function validateExists(string $field, $value, array $params): bool
    {
        if (empty($value)) return true;
        
        $table = $params[0] ?? $field . 's';
        $column = $params[1] ?? 'id';
        
        $db = Database::getInstance();
        $count = $db->fetchColumn(
            "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value",
            ['value' => $value]
        );
        
        return $count > 0;
    }
}
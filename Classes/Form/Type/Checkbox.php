<?php
declare(strict_types=1);

namespace Form\Type;
use Form\Type\Input;

final class Checkbox extends Input {
    protected string $type = 'checkbox'; 
    private string $label;
    private ?string $customId = null; // Stocker un ID personnalisé, s'il est défini

    public function __construct(
        string $name,
        string $label,
        string $value,
        bool $required = false
    ) {
        parent::__construct($name, $required, $value);
        $this->label = $label;
    }

    public function setId(string $id): void {
        $this->customId = $id;
    }

    public function getId(): string {
        // Utiliser l'ID personnalisé si défini, sinon l'ID généré par la classe parente
        return $this->customId ?? parent::getId();
    }

    public function render(): string {
        return sprintf(
            '<input type="%s" %s value="%s" name="%s" id="%s"/><label for="%s">%s</label>',
            $this->type,
            $this->isRequired() ? 'required="required"' : '',
            htmlspecialchars($this->getValue()),
            $this->getName(),
            $this->getId(),
            $this->getId(),
            htmlspecialchars($this->label)
        );
    }
}

?>
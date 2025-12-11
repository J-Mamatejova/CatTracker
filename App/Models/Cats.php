<?php

namespace App\Models;

use Framework\Core\Model;

class Cats extends Model
{

    // Columns from docker/sql/01-init-cats.sql
    protected ?int $id = null;
    protected ?string $meno = null;       // name
    protected ?string $text = null;       // description/text
    protected ?string $status = null;     // status
    protected ?int $kastrovana = 0;       // tinyint(1) 0/1
    protected ?string $fotka = null;      // photo path
    // owner (optional) - may not exist in older DBs; controller will add column if needed
    protected ?int $user_id = null;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMeno(): ?string
    {
        return $this->meno;
    }

    public function setMeno(string $meno): void
    {
        $this->meno = $meno;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isKastrovana(): bool
    {
        return (bool)$this->kastrovana;
    }

    public function setKastrovana(int $flag): void
    {
        $this->kastrovana = $flag ? 1 : 0;
    }

    public function getFotka(): ?string
    {
        return $this->fotka;
    }

    public function setFotka(string $fotka): void
    {
        $this->fotka = $fotka;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $id): void
    {
        $this->user_id = $id;
    }

    // Optional: convenience to get an array ready for view (if needed)
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'meno' => $this->meno,
            'text' => $this->text,
            'status' => $this->status,
            'kastrovana' => $this->kastrovana,
            'fotka' => $this->fotka,
            'user_id' => $this->user_id,
        ];
    }
}
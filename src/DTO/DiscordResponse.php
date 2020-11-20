<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\DTO;

final class DiscordResponse
{
    private ?string $content = null;
    private ?string $title = null;
    private ?string $description = null;
    private ?string $url = null;
    private ?int $color = 7506394;
    private ?\DateTimeInterface $timestamp = null;
    private ?string $footer_icon = 'https://esoraidplanner.com/build/images/favicon/apple-icon.png';
    private ?string $footer_text = 'ESORaidplanner.com by Woeler';
    private ?string $thumbnail = null;
    private ?string $image = null;
    private ?string $author_name = null;
    private ?string $author_url = null;
    private ?string $author_icon = null;
    private array $fields = [];
    private bool $tts = false;
    private bool $onlyText = false;

    public function jsonSerialize(): array
    {
        return $this->onlyText ? ['content' => $this->content] : [
            'content'    => $this->content,
            'tts'        => $this->tts,
            'embed'     => [
                'title'       => $this->title,
                'description' => $this->description,
                'timestamp'   => null === $this->timestamp ? null : $this->timestamp->format(\DateTime::ATOM),
                'url'         => $this->url,
                'color'       => $this->color,
                'author'      => [
                    'name'     => $this->author_name,
                    'url'      => $this->author_url,
                    'icon_url' => $this->author_icon,
                ],
                'image' => [
                    'url' => $this->image,
                ],
                'thumbnail' => [
                    'url' => $this->thumbnail,
                ],
                'fields' => $this->fields,
                'footer' => [
                    'text'     => $this->footer_text,
                    'icon_url' => $this->footer_icon,
                ],
            ],
        ];
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setColor(?int $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function setTimestamp(?\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function setFooterIcon(?string $footer_icon): self
    {
        $this->footer_icon = $footer_icon;

        return $this;
    }

    public function setFooterText(?string $footer_text): self
    {
        $this->footer_text = $footer_text;

        return $this;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function setAuthorName(?string $author_name): self
    {
        $this->author_name = $author_name;

        return $this;
    }

    public function setAuthorUrl(?string $author_url): self
    {
        $this->author_url = $author_url;

        return $this;
    }

    public function setAuthorIcon(?string $author_icon): self
    {
        $this->author_icon = $author_icon;

        return $this;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function setTts(bool $tts): self
    {
        $this->tts = $tts;

        return $this;
    }

    public function withTimeStamp(): self
    {
        $this->timestamp = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    public function addField(string $name, string $value, bool $inLine = false): self
    {
        $this->fields[] = ['name' => $name, 'value' => $value, 'inline' => $inLine];

        return $this;
    }

    public function isOnlyText(): bool
    {
        return $this->onlyText;
    }

    public function setOnlyText(bool $onlyText): DiscordResponse
    {
        $this->onlyText = $onlyText;

        return $this;
    }
}

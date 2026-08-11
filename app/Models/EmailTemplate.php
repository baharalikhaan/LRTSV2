<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'signature',
        'category',
    ];

    /**
     * Available placeholder tags that can be used in subject/body.
     */
    public static function availablePlaceholders(): array
    {
        return [
            '*name*'          => 'Recipient name',
            '*email*'         => 'Recipient email',
            '*old_project_id*' => 'Project ID',
            '*project_title*'  => 'Project title',
            '*cycle*'          => 'Cycle year',
            '*deadline*'       => 'Deadline date',
            '*grant_title*'    => 'Grant title',
            '*link*'           => 'Action link',
        ];
    }

    /**
     * Available categories.
     */
    public static function categories(): array
    {
        return [
            'general'      => 'General',
            'reminder'     => 'Reminder',
            'notification' => 'Notification',
            'welcome'      => 'Welcome',
        ];
    }

    /**
     * Replace placeholders in the given text with provided data.
     */
    public function render(array $data = []): string
    {
        $replacements = array_merge(
            array_fill_keys(array_keys(self::availablePlaceholders()), ''),
            $data
        );

        $text = $this->body;
        foreach ($replacements as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }
}

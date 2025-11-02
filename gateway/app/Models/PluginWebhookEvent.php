<?php

namespace App\Models;

use App\Enums\PluginStatus;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PluginWebhookEvent extends Model
{
    use HasFactory, UsesSystemConnection;

    protected $fillable = [
        'hostname_id',
        'model_type',
        'model_id',
        'event_type',
        'payload',
        'plugin_config',
        'status',
        'attempts',
        'response',
        'error_message',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'plugin_config' => 'array',
        'response' => 'array',
        'status' => PluginStatus::class,
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the hostname this webhook belongs to
     */
    public function hostname(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Hostname::class);
    }

    /**
     * Get the model that triggered this webhook
     * Note: This will need tenant context to work properly
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the model with tenant context
     */
    public function getModelWithTenantContext()
    {
        if (!$this->model_type || !$this->model_id) {
            return null;
        }

        // Switch to tenant context
        $hostname = $this->hostname;
        if (!$hostname || !$hostname->website) {
            return null;
        }

        $originalWebsite = \Hyn\Tenancy\Facades\TenancyFacade::website();

        try {
            \Hyn\Tenancy\Facades\TenancyFacade::website($hostname->website);

            // Find the model in tenant database
            $modelClass = $this->model_type;
            if (class_exists($modelClass)) {
                return $modelClass::find($this->model_id);
            }

            return null;

        } finally {
            // Restore original context
            \Hyn\Tenancy\Facades\TenancyFacade::website($originalWebsite);
        }
    }

    /**
     * Scope for pending events
     */
    public function scopePending($query)
    {
        return $query->where('status', PluginStatus::PENDING);
    }

    /**
     * Scope for failed events
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY]);
    }

    /**
     * Scope for specific hostname
     */
    public function scopeForHostname($query, $hostnameId)
    {
        return $query->where('hostname_id', $hostnameId);
    }

    /**
     * Scope for specific plugin
     */
    public function scopeForPlugin($query, string $pluginName)
    {
        return $query->whereJsonContains('plugin_config->plugin_name', $pluginName);
    }

    /**
     * Check if event can be retried
     */
    public function canRetry(): bool
    {
        return $this->status === PluginStatus::FAILED && $this->attempts < 3;
    }

    /**
     * Get success rate for this hostname/plugin combination
     */
    public static function getSuccessRateForHostname(int $hostnameId, ?string $pluginName = null): float
    {
        $query = static::where('hostname_id', $hostnameId);

        if ($pluginName) {
            $query->whereJsonContains('plugin_config->plugin_name', $pluginName);
        }

        $total = $query->count();
        if ($total === 0) {
            return 0.0;
        }

        $successful = $query->where('status', PluginStatus::COMPLETED)->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get recent failed webhooks for a hostname
     */
    public static function getRecentFailuresForHostname(int $hostnameId, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('hostname_id', $hostnameId)
            ->whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY])
            ->where('created_at', '>=', now()->subHours($hours))
            ->latest()
            ->get();
    }

    /**
     * Check if this webhook is in a final state
     */
    public function isFinal(): bool
    {
        return in_array($this->status, [
            PluginStatus::COMPLETED,
            PluginStatus::FAILED_PERMANENTLY
        ]);
    }

    /**
     * Check if this webhook is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === PluginStatus::PROCESSING;
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => PluginStatus::FAILED,
            'error_message' => $errorMessage,
            'failed_at' => now(),
        ]);
    }

    /**
     * Mark webhook as completed
     */
    public function markAsCompleted(array $response = []): void
    {
        $this->update([
            'status' => PluginStatus::COMPLETED,
            'response' => $response,
            'completed_at' => now(),
        ]);
    }
}

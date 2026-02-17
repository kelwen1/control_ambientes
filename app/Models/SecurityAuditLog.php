<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    /**
     * Tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'security_audit_logs';

    /**
     * Clave primaria.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo debe manejar timestamps.
     *
     * En la tabla solo usamos created_at para la marca de tiempo.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nombre de la columna de "created at".
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * No usamos updated_at en esta tabla.
     *
     * @var null
     */
    const UPDATED_AT = null;

    /**
     * Atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'ip_address',
        'user_agent',
        'status',
        'metadata',
        'created_at',
    ];
}


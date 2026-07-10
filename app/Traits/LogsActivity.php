<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            self::logActivity('created', $model);
        });

        static::updated(function (Model $model) {
            self::logActivity('updated', $model);
        });

        static::deleted(function (Model $model) {
            self::logActivity('deleted', $model);
        });
    }

    protected static function logActivity(string $action, Model $model)
    {
        $user = Auth::user();
        if (!$user) {
            return; // Skip if no logged-in user (e.g. CLI seeders)
        }

        $before = null;
        $after = null;

        if ($action === 'updated') {
            $changes = $model->getChanges();
            if (isset($changes['password'])) {
                $changes['password'] = '******';
            }
            $after = $changes;

            $original = array_intersect_key($model->getOriginal(), $changes);
            if (isset($original['password'])) {
                $original['password'] = '******';
            }
            $before = $original;
        } elseif ($action === 'created') {
            $attributes = $model->getAttributes();
            if (isset($attributes['password'])) {
                $attributes['password'] = '******';
            }
            $after = $attributes;
        } elseif ($action === 'deleted') {
            $attributes = $model->getAttributes();
            if (isset($attributes['password'])) {
                $attributes['password'] = '******';
            }
            $before = $attributes;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => strtoupper($action),
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'before' => $before,
            'after' => $after,
            'description' => self::getActivityDescription($action, $model),
            'ip_address' => request()->ip(),
        ]);
    }

    protected static function getActivityDescription(string $action, Model $model): string
    {
        $className = class_basename($model);
        
        switch ($className) {
            case 'HafalanSantri':
                $santriName = $model->santri?->nm ?? 'Santri ID ' . $model->santri_id;
                $hafalanName = $model->dataHafalan?->nama_hafalan ?? 'Hafalan ID ' . $model->data_hafalan_id;
                if ($action === 'created') {
                    return "Menandai hafalan '{$hafalanName}' tuntas untuk santri '{$santriName}'";
                }
                return "Membatalkan setoran hafalan '{$hafalanName}' untuk santri '{$santriName}'";
                
            case 'User':
                if ($action === 'created') {
                    return "Membuat akun pengguna baru '{$model->name}' ({$model->email})";
                } elseif ($action === 'updated') {
                    return "Mengubah profil/password akun pengguna '{$model->name}' ({$model->email})";
                }
                return "Menghapus akun pengguna '{$model->name}' ({$model->email})";
                
            case 'Mustahiq':
                if ($action === 'created') {
                    return "Menambahkan Mustahiq baru '{$model->nama_mustahiq}'";
                } elseif ($action === 'updated') {
                    return "Mengubah data Mustahiq '{$model->nama_mustahiq}'";
                }
                return "Menghapus data Mustahiq '{$model->nama_mustahiq}'";
                
            case 'TahunAjaran':
                if ($action === 'created') {
                    return "Menambahkan Tahun Ajaran baru '{$model->nama_tahun_ajaran}'";
                } elseif ($action === 'updated') {
                    return "Mengubah data Tahun Ajaran '{$model->nama_tahun_ajaran}'";
                }
                return "Menghapus data Tahun Ajaran '{$model->nama_tahun_ajaran}'";
                
            case 'DataHafalan':
                if ($action === 'created') {
                    return "Menambahkan target hafalan baru '{$model->nama_hafalan}'";
                } elseif ($action === 'updated') {
                    return "Mengubah data target hafalan '{$model->nama_hafalan}'";
                }
                return "Menghapus data target hafalan '{$model->nama_hafalan}'";
                
            default:
                return "Melakukan aksi " . strtoupper($action) . " pada data {$className} (ID: {$model->getKey()})";
        }
    }
}

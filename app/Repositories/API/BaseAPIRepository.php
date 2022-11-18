<?php

namespace App\Repositories\API;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;

abstract class BaseAPIRepository extends Repository
{
    /**
     * Get all Rows
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = ['*'])
    {
        return $this->query()->get($columns);
    }

    /**
     * Wrap model findOrFail method
     *
     * @param $id
     *
     * @return Model|mixed
     */
    public function findOrFail($id)
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * Wrap model find method
     *
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->query()->find($id);
    }

    /**
     * Find item by a special column
     *
     * @param $field
     * @param $value
     * @param $columns
     *
     * @return Model|null|mixed
     */
    public function findBy($field, $value, $columns = ['*'])
    {
        return $this->query()->where($field, $value)->first($columns);
    }

    /**
     * Create a new item
     *
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->query()->create($data);
    }

    /**
     * Update an existing model instance
     *
     * @param $item
     * @param $data
     * @return mixed
     */
    public function update($item, $data)
    {
        return $item->update($data);
    }

    /**
     * Create a new model when ignoring guarded attributes
     *
     * @param $data
     * @return Model
     */
    public function forceCreate($data)
    {
        $item = $this->getModel()->newInstance();

        $item->forceFill($data);

        $item->save();

        return $item;
    }

    /**
     * Update an existing model model when ignoring guarded attributes
     *
     * @param $data
     * @return Model
     */
    public function forceUpdate($item, $data)
    {
        $item->forceFill($data);

        $item->save();

        return $item;
    }

    /**
     * Pass all unknown function through base model
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(
            [$this->getModel(), $method],
            $args
        );
    }

    public function rememberToken()
    {
        return 'iys6BhFoXfdsn1o2VC1sddBDyII5D0';

        $request = request();

        if (!empty($request->customer_id)) {
            $user = User::query()->select(['remember_token'])->find($request->customer_id);

            if (!empty($user->remember_token)) {
                return $user->remember_token;
            }
        }

        return "";
    }
}

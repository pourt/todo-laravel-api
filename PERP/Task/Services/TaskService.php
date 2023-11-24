<?php

namespace PERP\Task\Services;

use PERP\Task\Models\Task;

class TaskService
{
    public $task = null;
    public function __constructor()
    {
    }

    public function create($request)
    {
        $this->task = Task::create($request);

        if (!$this->task) {
            throw new \Exception('Sorry, we are unable to create Task information', 400);
        }

        return $this;
    }


    public function get($whereClosure = null, $orderBy = ['created_at', 'asc'], $limit = null, $isTrashed = false)
    {
        $this->task = Task::where($whereClosure)
            ->orderBy($orderBy[0], $orderBy[1]);

        return $this;
    }

    public function list($paginate = true, $perPage = 15)
    {
        $perPage = request('perPage', $perPage);

        $this->task = $paginate ? $this->task->paginate($perPage) : $this->task->all();

        if (!$this->task) {
            throw new \Exception('Sorry, we are unable to retrieve task', 404);
        }

        return $this;
    }

    public function one()
    {
        $this->task = $this->task->first();

        if (!$this->task) {
            throw new \Exception('Sorry, we are unable to retrieve task', 404);
        }

        return $this;
    }

    public function update($request = [])
    {
        $this->task->fill($request);

        if (!$this->task->save()) {
            throw new \Exception('Sorry, we are unable to update Task information', 500);
        }

        return $this;
    }

    public function delete()
    {
        if (!$this->task->delete()) {
            throw new \Exception('Sorry, we are unable to delete Task information', 500);
        }

        return $this;
    }

    public function toJson()
    {
        return $this->task;
    }

    public function toArray()
    {
        return $this->task->toArray();
    }
}

<?php

namespace PERP\Task\Controllers;

use App\Http\Controllers\Controller;
use PERP\Task\FormRequests\NewTaskRequest;
use PERP\Task\Responses\Collection\TaskCollection;
use PERP\Task\Responses\Resource\TaskResource;
use PERP\Task\Services\TaskService;
use PERP\Traits\ApiResponser;

class TaskController extends Controller
{
    use ApiResponser;

    public function __constructor()
    {
    }

    public function index()
    {
        try {

            $orderBy = ['created_at', 'asc'];

            $whereClosure = null;

            $tasks = (new TaskService)->get($whereClosure, $orderBy)->list()->toJson();

        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new TaskCollection($tasks)),
            'Tasks successfully retrieved'
        );
    }

    public function show()
    {
        try {
            $whereClosure = function ($query) {
                return $query->where("id", request()->taskId);
            };

            $task = (new TaskService)->get($whereClosure)->one()->toJson();

        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());

        }

        return $this->success(
            (new TaskResource($task)),
            'Task successfully retrieved'
        );
    }

    public function store(NewTaskRequest $request)
    {
        try {

            $data = request()->toArray();

            $task = (new TaskService)->create($data)->toJson();

        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());

        }

        return $this->success(
            (new TaskResource($task)),
            'Task successfully created'
        );
    }

    public function update()
    {
        try {
            $where = function ($query) {
                $query->where('id', request()->taskId);
            };

            $data = request()->toArray();

            $task = (new TaskService)->get($where)->one()->update($data)->toJson();

        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());

        }

        return $this->success(
            (new TaskResource($task)),
            'Task successfully modified'
        );
    }

    public function destroy()
    {
        try {

            $where = function ($query) {
                $query->where('id', request()->taskId);
            };

            $task = (new TaskService)->get($where)->one()->delete()->toJson();

        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());

        }

        return $this->success(
            (new TaskResource($task)),
            'Task successfully removed'
        );
    }
}

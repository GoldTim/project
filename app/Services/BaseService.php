<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;

class BaseService
{
    protected $model;

    /**
     * 获取列表
     * @param $where - 条件
     * @param int $page - 页码
     * @param int $limit - 数量
     * @param string $sort - 排序字段
     * @param string $order - 升降序
     * @param array $field - 查询字段
     * @return array
     */
    public function getList($where, array $field = ["*"], int $page = 1, int $limit = 10, string $sort = "id", string $order = "desc"): array
    {
        $data = $this->model->where($where)->orderBy($sort, $order)->paginate($limit, $field, "page", $page);
        return [
            'count' => $data->total(),
            'data' => $data->items()
        ];
    }

    /**
     * 获取符合条件的所有数据
     * @param $where - 条件
     * @param string $sort - 排序条件
     * @param string $order - 升降
     * @return mixed
     */
    public function getAll($where, $field = ["*"], string $sort = "id", string $order = "desc")
    {
        return $this->model->where($where)->orderBy($sort, $order)->get($field);
    }

    /**
     * 根据条件获取指定第一条数据
     * @param $search
     * @param string $sort
     * @param string $order
     * @return mixed
     */
    public function getOneForSearch($search, string $sort = 'id', string $order = 'desc')
    {
        return $this->model->where($search)->order($sort, $order)->first();
    }

    /**
     * 根据条件获取总数居
     * @param array $where
     * @return mixed
     */
    public function getCount(array $where = [])
    {
        return $this->model->where($where)->count();
    }

    /**
     * 根据主键获取一条数据
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        return $this->model->find($id);
    }

    /**
     * 添加一条数据
     * @param $data
     * @return mixed
     */
    public function addOne($data)
    {
        $status = 2;
        try {
            $res = $this->model->create($data);
            $id = $res->id;
            $data['id'] = $id;
            if ($id > 0) {
                $status = 1;
            }
        } catch (Exception $e) {

            $id = 0;
            $data = [
                'data' => $data,
                'error_message' => $e->getMessage()
            ];
        }


        //后台日志记录
        $this->recordAdminLog('新增数据：', $data, $status);

        return $id;
    }

    /**
     * 添加多条数据
     * @param $data
     * @return mixed
     */
    public function addBatch($data)
    {
        $status = 2;
        try {
            $res = $this->model->insertAll($data);
            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $data = [
                'data' => $data,
                'error_message' => $e->getMessage()
            ];
        }

        //后台日志记录
        $this->recordAdminLog('批量新增数据：', $data, $status);

        return $res;
    }

    /**
     * 更新一条数据
     * @param $id
     * @param $data
     * @return mixed
     */
    public function renewal($id, $data)
    {
        $status = 2;
        try {
            $res = $this->model->where('id', '=', $id)->update($data);
            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $data = [
                'data' => $data,
                'error_message' => $e->getMessage()
            ];
        }
        //后台日志记录
        $this->recordAdminLog('更新数据：' . $id . ' - ', $data, $status);

        return $res;
    }

    /**
     * 更新多条数据
     * @param $where
     * @param $data
     * @return mixed
     */
    public function renewalByWhere($where, $data)
    {
        $status = 2;
        try {
            $res = $this->model->where($where)->update($data);
            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $data = [
                'data' => $data,
                'error_message' => $e->getMessage()
            ];
        }
        //后台日志记录
        $this->recordAdminLog('更新数据：' . json_encode($where) . ' - ', $data, $status);

        return $res;
    }

    /**
     * 批量更新数据
     * @param $data
     * @return mixed
     */
    public function renewalBatch($data)
    {
        $status = 2;
        try {
            $res = $this->model->saveAll($data);
            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $data = [
                'data' => $data,
                'error_message' => $e->getMessage()
            ];
            dd($e->getMessage());
        }
        //后台日志记录
        $this->recordAdminLog('批量更新数据：', $data, $status);

        return $res;
    }

    /**
     * 删除一条数据
     * @param $id
     * @return mixed
     */
    public function remove($id)
    {
        $status = 2;
        try {
            $where = [];
            if (is_array($id)) {
                $where[] = ['id', 'in', $id];
            } else {
                $where[] = ['id', '=', $id];
            }

            $model_name = $this->model->getName();

            $soft_delete_table_list = Config::get('constant.soft_delete_table_list');
            if (isset($soft_delete_table_list[$model_name])) {
                $soft_delete_table = $soft_delete_table_list[$model_name];

                $field = $soft_delete_table['field'];
                $value = $soft_delete_table['value'];
                if (empty($field) || empty($value)) {
                    return false;
                }

                $update_entity = [
                    $field => $value
                ];

                $time = $soft_delete_table['time'];
                if ($time) {
                    $update_entity[$time] = time();
                }

                $res = $this->model->where($where)->update($update_entity);

            } else {
                $res = $this->model->where($where)->delete();
            }

            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $id = [
                'id' => $id,
                'error_message' => $e->getMessage()
            ];
        }
        //后台日志记录
        $this->recordAdminLog('删除数据：', $id, $status);

        return $res;
    }

    /**
     * 删除多条数据
     * @param $where
     * @return mixed
     */
    public function removeByWhere($where)
    {
        $status = 2;
        try {

            $model_name = $this->model->getName();

            $soft_delete_table_list = Config::get('constant.soft_delete_table_list');
            if (isset($soft_delete_table_list[$model_name])) {
                $soft_delete_table = $soft_delete_table_list[$model_name];

                $field = $soft_delete_table['field'];
                $value = $soft_delete_table['value'];
                if (empty($field) || empty($value)) {
                    return false;
                }

                $update_entity = [
                    $field => $value
                ];
                $time = $soft_delete_table['time'];
                if ($time) {
                    $update_entity[$time] = time();
                }
                $res = $this->model->where($where)->update($update_entity);
            } else {
                $res = $this->model->where($where)->delete();
            }

            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $where = [
                'where' => $where,
                'error_message' => $e->getMessage()
            ];
        }
        //后台日志记录
        $this->recordAdminLog('批量删除数据：', $where, $status);

        return $res;
    }

    /**
     * 数据增加
     * @param $id
     * @param $field
     * @param $num
     * @return mixed
     */
    public function setInc($id, $field, $num = 1)
    {
        $data = [$field, $num];
        $status = 2;
        try {
            $res = $this->model->where('id', '=', $id)->inc($field, $num)->update();
            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $data = [
                'data' => [$field, $num],
                'error_message' => $e->getMessage()
            ];
        }
        //后台日志记录
        $this->recordAdminLog('数据增加：' . $id . ' - ', $data, $status);

        return $res;
    }

    /**
     * 数据减少
     * @param $id
     * @param $field
     * @param $num
     * @return mixed
     */
    public function setDec($id, $field, $num = 1)
    {
        $data = [$field, $num];
        $status = 2;
        try {
            $res = $this->model->where('id', '=', $id)->dec($field, $num)->update();
            if ($res) {
                $status = 1;
            }
        } catch (Exception $e) {
            $res = 0;
            $data = [
                'data' => [$field, $num],
                'error_message' => $e->getMessage()
            ];
        }
        //后台日志记录
        $this->recordAdminLog('数据减少：' . $id . ' - ', $data, $status);

        return $res;
    }

    /**
     * 记录系统日志
     * @param $prefix
     * @param $data
     * @param $status
     */
    protected function recordAdminLog($prefix, $data, $status = 0)
    {

    }
}

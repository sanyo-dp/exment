<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Controllers\HasResourceActions as ParentResourceActions;

trait HasResourceActions
{
    use ParentResourceActions;
    
    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return $this->form($id)->update($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (method_exists($this, 'validateDestroy')) {
            $data = $this->validateDestroy($id);
            if (!empty($data)) {
                return response()->json($data);
            }
        }

        $rows = collect(explode(',', $id))->filter();
            
        // check row's disabled_delete
        $disabled_delete = false;
        $rows->each(function ($id) use (&$disabled_delete) {
            if (!$disabled_delete) {
                $model = $this->form($id)->model()->find($id);

                if (boolval(array_get($model, 'disabled_delete'))) {
                    $disabled_delete = true;
                }
            }
        });

        if ($disabled_delete) {
            return response()->json([
                'status'  => false,
                'message' => exmtrans('error.disable_delete_row'),
                'reload' => false,
            ]);
        }

        $result = true;
        $rows->each(function ($id) use (&$result) {
            if (method_exists($this, 'widgetDestroy')) {
                if (!$this->widgetDestroy($id)) {
                    $result = false;
                    return;
                }
            } elseif (!$this->form($id)->destroy($id)) {
                $result = false;
                return;
            }
        });

        if ($result) {
            $data = [
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ];
        } else {
            $data = [
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ];
        }

        return response()->json($data);
    }
}

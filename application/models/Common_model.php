<?php
class Common_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function getRecordData($table, $custom_where = '', $select_column = '', $order_by = array(), $join_array = array(), $group_by = '', $excludeIds = array(),    $limit = '')
	{
		if ($select_column != '') {
			$this->db->select($select_column);
		}
		$this->db->from($table);
		if (is_array($join_array)) {
			foreach ($join_array as $val) {
				$this->db->join($val['table1'], $val['table1Val'] . '=' . $val['table2Val'], $val['joinType']);
			}
		}
		if ($custom_where != '') {
			$this->db->where($custom_where);
		}
		if (is_array($excludeIds)) {
			if (!empty($excludeIds)) {
				$this->db->where_not_in($excludeIds[0], $excludeIds[1]);
			}
		}
		if (is_array($order_by)) {
			foreach ($order_by as $key => $val) {
				$this->db->order_by($key, $val);
			}
		}
		if ($group_by != '') {
			$this->db->group_by($group_by);
		}
		if ($limit != '') {
			$this->db->limit($limit);
		}
		$query = $this->db->get();
		return $query->result_array();
	}

	/* Insert data in table */
	function insert($data, $table)
	{
		$this->db->insert($table, $this->security->xss_clean($data));
		$error = $this->db->error();
		if ($error['code'] == 00000 && trim($error['message']) == '') {
			$rData = $this->db->insert_id();
		} else {
			$rData = '<div class="alert alert-danger">[Error:' . $error['code'] . '] unable to insert data</div>';
		}

		return $rData;
	}
	/* //Insert data in table */


	/* update data in table */
	function update($id, $data, $colum_name, $table)
	{
		$colum_name = (string)$colum_name;
		$id = (int)$id;
		$this->db->where($colum_name, $id);
		$this->db->update($table, $this->security->xss_clean($data));
		$error = $this->db->error();
		if ($error['code'] == 00000 && trim($error['message']) == '') {
			$rData = 1;
		} else {
			$rData = '<div class="alert alert-danger">[Error:' . $error['code'] . '] unable to update data</div>';
		}
		return $rData;
	}
	/* //update data in table */

	function delete($ID, $colum_name, $table)
	{
		$colum_name = (string)$colum_name;
		$ID = (int)$ID;
		$this->db->where($colum_name, $ID);
		$this->db->delete($table);
		$error = $this->db->error();
		if ($error['code'] == 00000 && trim($error['message']) == '') {
			$rData = 1;
		} else {
			$rData = '<div class="alert alert-danger">[Error:' . $error['code'] . '] Unable to delete data</div>';
		}
		return $rData;
	}
}

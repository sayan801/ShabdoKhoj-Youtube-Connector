<?php

class Jobpost_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_jobpost($search = FALSE) {
/*
        if ($id === FALSE) {
            $query = $this->db->get('jobpost');
            return $query->result_array();
        }

        $query = $this->db->get_where('jobpost', array('id' => $id));
        return $query->row_array();
*/
	if ($search === FALSE) {
            $query = $this->db->get('jobpost');
            return $query->result_array();
        }
		$sql="SELECT * FROM jobpost where id = '".$search."' or degree= '".$search."'";
	  
		$sql1=mysql_query($sql);
	 //return $query->result_array($sql1);
	 
	 while ($ar=mysql_fetch_array($sql1))
		{
			echo $ar['id'];
			echo $ar['degree'];
			
			echo "<br>";
		 }
    }

    public function set_jobpost() {
        $this->load->helper('url');
                
        $data = array(
            'degree' => $this->input->post('degree'),
            'school' => $this->input->post('school'),
            'dateGraduated' => $this->input->post('dateGraduated'),
            'certification' => $this->input->post('certification'),
            'notes' => $this->input->post('notes'),
            'emr' => $this->input->post('emr'),
            'emrModules' => $this->input->post('emrModules'),
            'trainingLevel' => $this->input->post('trainingLevel'),
            'yearsExperience' => $this->input->post('yearsExperience'),
            'employmentType' => $this->input->post('employmentType'),
            'employment' => $this->input->post('employment'),
            'travel' => $this->input->post('travel'),
            'requiredPay' => $this->input->post('requiredPay'),
            'amount' => $this->input->post('amount'),
            'preferredTravelRegion' => $this->input->post('preferredTravelRegion')              
           );

        return $this->db->insert('jobpost', $data);
    }
    
    
    public function get_results($search_term='default')
    {
        // Use the Active Record class for safer queries.
        $this->db->select('*');
        $this->db->from('jobpost');
        $this->db->like('degree',$search_term);
        $this->db->or_like('certification',$search_term);
        $this->db->or_like('amount',$search_term);
        $this->db->or_like('requiredPay',$search_term);
        $this->db->or_like('yearsExperience',$search_term);
        
        
        // Execute the query.
        $query = $this->db->get();
        // Return the results.
        return $query->result_array();
    }

}

?>

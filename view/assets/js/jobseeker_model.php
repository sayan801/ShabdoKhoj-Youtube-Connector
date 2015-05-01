<?php

class Jobseeker_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_jobseeker($search = FALSE) {
/*
        if ($id === FALSE) {
            $query = $this->db->get('jobseekerinfo');
            return $query->result_array();
        }

        $query = $this->db->get_where('jobseekerinfo', array('id' => $id));
        return $query->row_array();
*/

		if ($search === FALSE) {
            $query = $this->db->get('jobseekerinfo');
            return $query->result_array();
        }
	  $sql="SELECT * FROM jobseekerinfo where id = '".$search."' or firstName= '".$search."'";
	  
	 $sql1=mysql_query($sql);
	 //return $query->result_array($sql1);
	 
	 while ($ar=mysql_fetch_array($sql1))
	{
	echo $ar['id'];
	echo $ar['firstName'];
	echo $ar['middleInitial'];
	echo "<br>";
		 }
    }

    public function set_jobseeker() {
        $this->load->helper('url');
        //substr(implode(', ', $this->input->post('preferredTravelRegion')), 0);
        //print_r( $this->input->post('preferredTravelRegion'));
        //$preferredTravelRegion =  $this->input->post('preferredTravelRegion');



        $data = array(
            'firstName' => $this->input->post('firstName'),
            'middleInitial' => $this->input->post('middleInitial'),
            'lastName' => $this->input->post('lastName'),
            'address1' => $this->input->post('address1'),
            'address2' => $this->input->post('address2'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'region' => $this->input->post('region'),
            'zip' => $this->input->post('zip'),
            'phone1' => $this->input->post('phone1'),
            'phone2' => $this->input->post('phone2'),
            'email1' => $this->input->post('email1'),
            'email2' => $this->input->post('email2'),
            'highestDegree' => $this->input->post('highestDegree'),
            'degreeDetails' => $this->input->post('degreeDetails'),
            'school' => $this->input->post('school'),
            'dateGraduated' => $this->input->post('dateGraduated'),
            'certifications' => $this->input->post('certifications'),
            'certificationNotes' => $this->input->post('certificationNotes'),
            'emrTraining' => $this->input->post('emrTraining'),
            'emrModules' => $this->input->post('emrModules'),
            'trainingLevel' => $this->input->post('trainingLevel'),
            'yearsOfExperience' => $this->input->post('yearsOfExperience'),
            'employmentType' => $this->input->post('employmentType'),
            'employmentTimingType' => $this->input->post('employmentTimingType'),
            'travelChoice' => $this->input->post('travelChoice[]'),
            'paymentType' => $this->input->post('paymentType'),
            'paymentAmount' => $this->input->post('paymentAmount'),
            'preferredTravelRegion' => $this->input->post('$preferredTravelRegion')
        );

        return $this->db->insert('jobseekerinfo', $data);
    }
    
    public function get_results($search_term='default')
    {
        // Use the Active Record class for safer queries.
        $this->db->select('*');
        $this->db->from('jobseekerinfo');
        $this->db->like('firstName',$search_term);
        $this->db->or_like('middleInitial',$search_term);
        $this->db->or_like('lastName',$search_term);
        
        $this->db->or_like('state',$search_term);
        
        $this->db->or_like('highestDegree',$search_term);
        
        $this->db->or_like('certifications',$search_term);
        
        $this->db->or_like('yearsOfExperience',$search_term);
        
        $query = $this->db->get();
        // Return the results.
        return $query->result_array();
    }

}

?>

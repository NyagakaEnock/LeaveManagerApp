<?php
class Blog_model extends CI_Model {

		public function __construct()
        {
                $this->load->database();
        }
		public function validateBlog()
		 {
			
			 $state= FALSE;
			 IF($this->cleanData($this->input->get('subject'))==NULL)
			 {
				 echo "<font color='red'>Blog Subject Required.</font>";
				  $state= FALSE;
			 }elseif($this->cleanData($this->input->get('content'))==NULL)
			 {
				  echo "<font color='red'>Blog Content Required.</font>";
				  $state= FALSE;			 
			 }else{
				 $state = TRUE;
			 }
			 return $state;
		 }
		 
		public function updateBlog( )
		{
				$data = array(
				'title' => $this->cleanData($this->input->get('subject')),
				'content' => $this->cleanData($this->input->get('content'))
				
				);	
					$this->db->where('id',$this->input->get('productid'));
				$this->db->update('blog', $data);	
				echo "Changes Successfully";	
		}
	public function cleanData($input)
		 {
		 $cleanData = $this->security->xss_clean($input );
		 $cleanData = str_replace(array("<xml>","</xml>","'","<script>","</script>"),array(" "," ","`","<!--","-->"),$cleanData);
		 return $cleanData;
		 }
		public function saveBlog()
		{
	
				$data = array(
				'title' => $this->cleanData($this->input->get('subject')),
				'content' => $this->cleanData($this->input->get('content')),
				'dateposted' => date('Y-m-d'),
				'postedby' => $_SESSION['FortuneUser'],
				'status' => 'Y',
				'urldesc' => str_replace(" ","_",$this->cleanData($this->input->get('subject')))
				);	
				
				$this->db->insert('blog', $data);	
				echo "Post Published Successfully";	
			
		}
		public function getPosts($id)
		 {
			 if($id!="")
			 {
				$query = $this->db->query("SELECT * FROM blog WHERE id='{$id}' "); 
			 }else{
			$query = $this->db->query("SELECT * FROM blog ");
			 }
			return $query->result();	
		 }
		public function checkIfLoggedIn()
		{
			if(isset($_SESSION['FortuneUser']))
			{
				return True;
			}else{
				return false;
			}
		
		}
}
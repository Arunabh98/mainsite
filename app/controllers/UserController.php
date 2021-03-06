<?php
use Illuminate\Support\MessageBag;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use GuzzleHttp\Client;

class UserController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function fblogin(){


		$app_id='339712599560349';
		$code=Input::get('code');
		$redirect_url=URL::to('/').'/fblogin';
		$app_secret='3c50091370251ec12e280a7af1952572';
		$token_url = "https://graph.facebook.com/oauth/access_token?"
       . "client_id=" . $app_id . "&redirect_uri=" . urlencode($redirect_url)
       . "&client_secret=" . $app_secret . "&code=" . $code;
    	
    	try{
       	$response = file_get_contents($token_url);	
       }
       catch (Exception $e) {
       		return "problem";
       		       }
       $params = null;
     	parse_str($response, $params);
     	$graph_url = "https://graph.facebook.com/me?access_token=" 
       . $params['access_token'];

     	$newUser = json_decode(file_get_contents($graph_url));
   			
     	
     	if(!isset($newUser->email)){
     		$messageBag = new MessageBag;
			$messageBag->add('email.absent', 'We are unable to fetch your email id. Go to <a href="https://www.facebook.com/settings?tab=applications"> Fb Setting </a> , search for app "STAB-IITB" , and allow the app to fetch email. ');
     		return Redirect::to('/')
				->withErrors($messageBag);
     	}

     	$email = $newUser->email;
		$users = User::where('email','=', $email)->get();

		if(sizeof($users) == 0){
			$user = new User;
			$user->email = $email;
			$user->name = $newUser->name;
			$user->fbid = $newUser->id;
			$user->save();
			Auth::login($user);
			return Redirect::route('user.profile');
		}
		User::where('id','=',$users[0]->id)
				->update(array('fbid' => $newUser->id));
		
		$user = $users[0];
		Auth::login($user);
		if($user->ldap_verified==0){
			return Redirect::route('user.profile');
		}

     	return Redirect::to('/'); 	
	}
	
	// Logout
	public function logout(){
		if(Auth::check()) Auth::logout();
		return Redirect::to('/');
	}

	public static function LoginURL(){
		session_start();
        $redirect_url=URL::to('/').'/fblogin';
        
        FacebookSession::setDefaultApplication('339712599560349', '3c50091370251ec12e280a7af1952572');
        $helper = new FacebookRedirectLoginHelper($redirect_url);
        $loginUrl=$helper->getLoginUrl(array('scope' => 'email'));
        return $loginUrl;
	}

	public function profile(){
		if(Input::has('key')){
			$key='Prateek';
			$encrypted=Input::get('key');
	 		$decrypted=rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		
			$user=User::find($decrypted);
			if(is_null($user)){
				return "Wrong Key.";
			}
			else{
				User::where('id','=',$decrypted)->update(
				array('ldap_verified'=>1));
				return "Account Successfully verified";
			}
		}
		$user =  Auth::User();
		View::share('user',$user);
		return View::make('user.profile');
	}

	public function edit(){
		$user=Auth::User();
		$user->facad=NULL;
		$user->want_room=NULL;
		$user->save();
		return Redirect::back();
	}

	public function verify(){
		if(Auth::User()->other_email == ""){
			$gpo_id=Input::get('gpo_id');
		}
		else{
			$gpo_id=Auth::User()->ldap_email;
		}
		$gpo_id = explode('@', $gpo_id)[0];
		$user=Auth::User();
		//var_dump($user);
		$key = 'Prateek';
		$string =$user->id;
		$user->ldap_email=$gpo_id."@iitb.ac.in";
		User::where('id','=',$string)->update(array('ldap_email'=>$gpo_id.'@iitb.ac.in'));

		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
		$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

		//var_dump($encrypted);
		//var_dump($decrypted);
		//echo $gpo_id;
		try {
			 Mail::send('email.verifygpo', ['key' => URL::Route('user.profile').'?key='.urlencode($encrypted),'name'=>$user->Name], function($message) use($user)
			 {
	     		$message->to($user->ldap_email, $user->Name)->subject('Verify Stab Id');
			 });
			$messageBag = new MessageBag;
			$messageBag->add('message',"We have sent you an email regarding account activation on your gpo id ".$user->ldap_email." .Click on the link to verify." );
			return Redirect::Route('user.profile')->with('messages', $messageBag)->withInput();
		} catch (Exception $e) {
			return $e->getMessage();
		}		
	}

	public function update()
	{
		
		if(Auth::check()){
			$user=Auth::User();
			if(Input::get('name') !="" && Input::get('roll_no') !="" && Input::get('dept') !="" && Input::get('hostel') !="" && Input::get('contact') !="" && Input::get('facad') !="" && Input::get('facad_ldap') !=""){

				$messageBag = new MessageBag;
				$messageBag->add('message',"Form filled successfully." );
				$user->saveFromInput(Input::all());
				$user->save();
				return Redirect::back();		
				}		
			else{
				$messageBag = new MessageBag;
				$messageBag->add('message',"Error in details." );
				return Redirect::back()->with('messages',$messageBag);
			}
		}
		else{
			$messageBag = new MessageBag;
			$messageBag->add('message',"Error in details." );
			return Redirect::back()->with('messages',$messageBag);
		}	
	}

	public function show_users()
		{	if(Auth::check()){
			if(Auth::User()->mentor==1 || Auth::User()->admin==1 || true){
				$users=User::get();
					if( sizeof($users)==0){
						return;
					};
					$users=$users->toArray();
					$attr = array('class'=>'table table-condensed table-hover table-striped table-bordered table-responsive', 'id'=>'myTbl');
					$t = new Table($users, $attr);
					$data= $t->build();
					return View::make('user.show_users',compact('data'));
			}
			return "You dont have required access.";	
		}
		return '<a href="'.UserController::LoginURL().'">Login</a>. to continue';
	}

	public function login_page()
	{
		if(Auth::check()){
			return Redirect::back();
		}
		else{
			return View::make('user.login_page');
		}
	}

	public function login(){
		$ldap=Input::get('ldap');
		$ldap = explode('@', $ldap)[0];
		$ldap=$ldap."@iitb.ac.in";
		$pwd=Input::get('password');
		
		$messageBag = new MessageBag;
		$bag_empty = true;

		if($ldap==""){
			$messageBag->add('message',"Please enter LDAP ID" );
			$bag_empty = false;
		}
		if($pwd==""){
			$messageBag->add('message',"Please enter Password" );
			$bag_empty = false;
		}

		if (! filter_var($ldap, FILTER_VALIDATE_EMAIL)) {
			$messageBag->add('message',"Please enter valid LDAP ID" );
			$bag_empty = false;
		}
		if(!$bag_empty){
			return Redirect::back()->with('messages', $messageBag);
		}

		$users = User::where('ldap_email','=', $ldap)->get();
		if(sizeof($users)<=0)
		{
			$messageBag->add('message',"User Doesnt Exist" );
			$bag_empty = false;
		}
		if(sizeof($users)>1)
		{
			$messageBag->add('message',"ERROR! Multiple Users" );
			$bag_empty = false;
		}
		if(!$bag_empty){
			return Redirect::back()->with('messages', $messageBag);
		}

		if($users[0]->password != sha1($pwd)){
			$messageBag->add('message',"Password doesn't match" );
			return Redirect::back()->with('messages', $messageBag);
		}
		else{
			if($users[0]->ldap_verified == 1){
				Auth::login($users[0]);
				return Redirect::to('/');
			}
			else{
				$messageBag->add('message',"Please Verify LDAP (GPO) ID First" );
				// return Redirect::back()->with('messages', $messageBag);
				Auth::login($users[0]);
				return Redirect::Route('user.profile')->with('messages', $messageBag);

			}
		}

	}

	public function signup(){

		$name=Input::get('name');
		$pwd=Input::get('password');
		$pwd_verify=Input::get('password_verify');
		$ldap=Input::get('ldap');
		$ldap = explode('@', $ldap)[0];
		$ldap=$ldap."@iitb.ac.in";
		$email=Input::get('email');

		$messageBag = new MessageBag;
		$bag_empty = true;

		if($name==""){
			$messageBag->add('message',"Please enter Name" );
			$bag_empty = false;
		}
		if($pwd==""){
			$messageBag->add('message',"Please enter Password" );
			$bag_empty = false;
		}
		if($ldap==""){
			$messageBag->add('message',"Please enter LDAP ID" );
			$bag_empty = false;
		}
		if($email==""){
			$messageBag->add('message',"Please enter Email" );
			$bag_empty = false;
		}

		if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
			$messageBag->add('message',"Only letters and white space allowed in Name" );
			$bag_empty = false;
		}
		if (! filter_var($ldap, FILTER_VALIDATE_EMAIL)) {
			$messageBag->add('message',"Please enter valid LDAP ID" );
			$bag_empty = false;
		}

		if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$messageBag->add('message',"Please enter valid Email" );
			$bag_empty = false;
		}
		if($pwd!=$pwd_verify){
			$messageBag->add('message',"Passwords do not Match" );
			$bag_empty = false;
		}
		if(!$bag_empty){
			return Redirect::back()->with('messages', $messageBag);
		}
		$users = User::where('ldap_email','=', $ldap)->get();

		if(sizeof($users) == 0){
			$user = new User;
			$user->other_email = $email;
			$user->name = $name;
			$user->password = sha1($pwd);
			$user->ldap_email=$ldap;
			$user->save();
			Auth::login($user);
			return Redirect::Route('user.profile');

			$gpo_id=$ldap;
			$gpo_id = explode('@', $gpo_id)[0];
			$user=Auth::User();
			//var_dump($user);
			$key = 'Prateek';
			$string =$user->id;
			$user->ldap_email=$gpo_id."@iitb.ac.in";
			User::where('id','=',$string)->update(array('ldap_email'=>$gpo_id.'@iitb.ac.in'));

			$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
			$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

			//var_dump($encrypted);
			//var_dump($decrypted);
			//echo $gpo_id;
			try {
				 Mail::send('email.verifygpo', ['key' => URL::Route('user.profile').'?key='.urlencode($encrypted),'name'=>$user->Name], function($message) use($user)
				 {
		     		$message->to($user->ldap_email, $user->Name)->subject('Verify Stab Id');
				 });
				$messageBag_new = new MessageBag;
				$messageBag_new->add('message',"We have sent you an email regarding account activation on your gpo id ".$user->ldap_email." .Click on the link to verify." );
				if(Auth::check()) Auth::logout();
				return Redirect::back()->with('messages', $messageBag_new)->withInput();
			} catch (Exception $e) {
				if(Auth::check()) Auth::logout();
				return $e->getMessage();
			}


			// return Redirect::route('user.profile');
		}
		else{
			$messageBag->add('message',"A User with the same LDAP ID Already Exists" );
			return Redirect::back()->with('messages', $messageBag);
		}
		
	}
	public function set_password()
	{
		$pwd=Input::get('password');
		$pwd_verify=Input::get('password_verify');
		$user=Auth::User();


		$messageBag = new MessageBag;
		$bag_empty = true;

		if($pwd==""){
			$messageBag->add('message',"Please enter Password" );
			$bag_empty = false;
		}

		if (strlen($_POST["password"]) < '8' || strlen($_POST["password"]) > '20') {
			$messageBag->add('message',"Password Must Contain At Least 8 and At Most 20 Characters" );
			$bag_empty = false;
	    }

		if($pwd!=$pwd_verify){
			$messageBag->add('message',"Passwords do not Match" );
			$bag_empty = false;
		}
		if(!$bag_empty){
			return Redirect::back()->with('messages', $messageBag);
		}

		try{
			User::where('id','=',$user->id)->update(array('password'=>sha1($pwd)));
			$messageBag1 = new MessageBag;
			$messageBag1->add('message',"Password Updated Successfully. \n You can now Login without fb also." );
			return Redirect::back()->with('messages', $messageBag1)->withInput();
		}
		catch (Exception $e) {
				return $e->getMessage();
		}
	}


	/*sso login*/
	public function  sso_login_redirect(){
		$code = $_GET["code"];
		$state = $_GET["state"];

		// post request for login
		$post_url = "http://gymkhana.iitb.ac.in/sso/oauth/token/";

		$data = array('code' => $code, 'redirect_uri' => URL::Route('sso_login_redirect'), 'grant_type' => 'authorization_code');

		$options = array(
    		'http' => array(
        		'header'  => "Host: gymkhana.iitb.ac.in\r\nAuthorization: Basic ".base64_encode("UjBW1n7gdAmBoP7OuUTSYEmTTW1FpPfnHuUgSukl:3GRX2fD8CGk3UHdwNbPHBkevCx965xQouUJI9qXsVtD9ptVDKhVQ2zawexxEyZGUtMVeARkAmyNwtDvZepPaaZUj1HbX8SA4RgXIH1AYBKqO8ZIkORHf1Y3bIKAaZrUS")."\r\nContent-type: application/x-www-form-urlencoded; charset=UTF-8",
        		'method'  => 'POST',
        		'content' => http_build_query($data),
    			),
			);
		$context  = stream_context_create($options);
		$result = file_get_contents($post_url, false,$context);
		if ($result === FALSE) {return Redirect::to('/');}
		$result = json_decode($result);
		$tokens = array('access_token' => $result->access_token,'refresh_token' => $result->refresh_token );
		$profile_data = UserController::fetch_profile($tokens);
		return View::make('user.sso_profile',compact('profile_data'));
	}
	public static function fetch_profile($tokens){
		
		$access_token = $tokens['access_token'];
		$refresh_token = $tokens['refresh_token'];
		$post_url = "http://gymkhana.iitb.ac.in/sso/user/api/user/?fields=first_name,last_name,type,profile_picture,sex,username,email,program,contacts,insti_address,secondary_emails,mobile,roll_number";
		$options = array(
    		'http' => array(
        		'header'  => "GET /sso/user/api/user/ HTTP/1.1\r\nHost: gymkhana.iitb.ac.in\r\nAuthorization: Bearer ".$access_token,
        		'method'  => 'GET',
    			),
			);
		$context  = stream_context_create($options);

		$result = file_get_contents($post_url, false,$context);

		// if ($result === FALSE) {echo "loll";}
		$result = json_decode($result);
		// var_dump($result);
		$id = $result->id;
		$dept = $result->program->department_name;
		$degree = $result->program->degree_name;
		$join_year = $result->program->join_year;
		$graduation_year = $result->program->graduation_year;
		$phone = $result->contacts[0]->number;
		$hostel = $result->insti_address->hostel_name;
		$room = $result->insti_address->room;
		$roll_number = $result->roll_number;
		$sex = $result->sex;
		$username = $result->username;
		$first_name = $result->first_name;
		$last_name = $result->last_name;
		$email = $result->email;
		$profile_data  = array('id' => $id,'dept'=>$dept,'degree'=>$degree,'join_year'=>$join_year,'graduation_year'=>$graduation_year,'phone'=>$phone,'hostel'=>$hostel,'room'=>$room,'roll_number'=>$roll_number,'sex'=>$sex,'username'=>$username,'first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email);
		return $profile_data;
	}

	public static function SSOLoginURL(){
		$client_id =  "UjBW1n7gdAmBoP7OuUTSYEmTTW1FpPfnHuUgSukl";
		$redirect_url = URL::Route('sso_login_redirect');
		$url = "http://gymkhana.iitb.ac.in/sso/oauth/authorize/?client_id=".$client_id."&response_type=code&scope=basic%20profile%20sex%20ldap%20picture%20phone%20insti_address%20program&redirect_uri=".$redirect_url."&state=some_state";
		return $url;
	}
}

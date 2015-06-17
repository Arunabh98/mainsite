@extends('layout.default')

@section('content')


		<!-- ============================================================= MAIN ============================================================= -->
		
		<main>
			
			<div class="container inner">
				<div class="row">
					
					<div class="col-md-3 inner-right-md inner-bottom-sm">
						
						<!-- ============================================================= SIDE NAVIGATION ============================================================= -->
							
						<ul class="sidenav">
							<li><a href="{{URL::Route('events.ITSP_2015.index')}}">Home</a></li>
							<li><a href="{{URL::Route('events.ITSP_2015.timeline')}}">Timeline</a></li>
							<li><a href="{{URL::Route('events.ITSP_2015.faq')}}">FAQ</a></li>
							<li><a href="{{URL::Route('events.ITSP_2015.discuss')}}">Discussion Forum</a></li>
							<!-- <li><a href="{{URL::Route('events.ITSP_2015.index')}}">Archive</a></li> -->
							<li><a href="{{URL::Route('events.ITSP_2015.about')}}">About</a></li>
							<!-- <li><a href="{{URL::Route('events.ITSP_2015.form')}}">Register</a></li> -->
							<!-- <li><a href="{{URL::Route('events.ITSP_2015.mentor')}}">Mentor Registration</a></li> -->
							<!-- <li><a href="{{URL::Route('events.ITSP_2015.abstract')}}">Abstract Submission</a></li> -->
							<!-- <li><a href="{{URL::Route('events.ITSP_2015.final_reviews')}}">Final Reviews</a></li> -->
							<li><a href="{{URL::Route('events.ITSP_2015.final_team_verification')}}">Final Team Verification</a></li>
							<li><a href="{{URL::Route('events.ITSP_2015.tshirt')}}">Tshirt Form</a></li>
						</ul><!-- /.sidenav -->
						
						<!-- ============================================================= SIDE NAVIGATION : END ============================================================= -->
						
					</div><!-- /.col -->
					
					<div class="col-md-9 inner-left-md border-left">
						@yield('inner-content')
					</div><!-- ./col -->
						
				</div><!-- /.row -->
			</div><!-- /.container -->
			
			
		</main>
		
		<!-- ============================================================= MAIN : END ============================================================= -->
		
@endsection

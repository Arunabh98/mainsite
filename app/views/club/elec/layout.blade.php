@extends('layout.default')

@section('clubcontent')


		<!-- ============================================================= MAIN ============================================================= -->
		
		<main>
			
			<div class="container inner">
				<div class="row">
					
					<div class="col-md-3 inner-right-md inner-bottom-sm">
						
						<!-- ============================================================= SIDE NAVIGATION ============================================================= -->
							
						<ul class="sidenav">
							<li><a href="{{URL::Route('electronics-club')}}">Home</a></li>
							<li><a href="{{URL::Route('electronics-club')}}/vision">Vision</a></li>
							<li><a href="{{URL::Route('electronics-club')}}/event">Events</a></li>
							<li><a href="{{URL::Route('electronics-club')}}/club-initiatives">Club Initiatives</a></li>
							<li><a href="{{URL::Route('electronics-club')}}/team">Team</a></li>
							<!--<li><a href="{{URL::Route('electronics-club')}}/gallery">Gallery</a></li>
							<li><a href="{{URL::Route('electronics-club')}}/video">Videos</a></li>-->
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>StoreKeeper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!--Font awesome Icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- datatable css -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.css" rel="stylesheet">
    @yield('header_css')
    <!-- --custom css -->
    <link rel="stylesheet" href="{{ asset('css/custom.css?1') }}">
</head>
<body>
   

<div class="wrapper">


    <div class="body-overlay"></div>
    
    
            @include('layouts.sidebar')
            
            
    
            <!-- Page Content  -->
            <div id="content">
            
                
                @include('layouts.nav')
                
                
                <div class="main-content">
                
                        @yield('main_content')
                        
                        @include('layouts.footer')
                        
                </div>
                        
                    
    
            </div>
        </div>
        
      
    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- datatable js -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.js"></script>
    


    @yield('footer_js_links')

    <script type="text/javascript">
  $(document).ready(function () {
    // Sidebar collapse toggle
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });
    

    // Mobile overlay toggle
    
    $('.body-overlay, .navbar-toggler').on('click', function () {
        $('#sidebar, .body-overlay').toggleClass('show-nav');
    });

  });
</script>
      
    @yield('footer_js')

</body>
</html>
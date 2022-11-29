@extends('layouts.master')

@section('title', 'Add Product')

@section('page-header')
    <i class="fa fa-plus-circle"></i> {{ request('upload') ? 'Upload' : 'Add' }} Product
@stop



@section('content')


    @include('products.product.include.add-modal')

    <div class="row">
        <div class="col-md-12">


            @include('partials._alert_message')




            <div class="widget-box">


                <!-- header -->
                <div class="widget-header">
                    <h4 class="widget-title"> @yield('page-header')</h4>
                    <span class="widget-toolbar">
                        @if (hasPermission('dokani.products.index', $slugs))
                            <a href="{{ request('upload') == 'product' ? route('dokani.product-uploads.index') : route('dokani.products.index') }}"
                                class="">
                                <i class="fa fa-list-alt"></i> Product List
                            </a>
                        @endif
                    </span>
                </div>



                <!-- body -->
                <div class="widget-body">
                    <div class="widget-main">



                        @if (request()->filled('upload'))
                            @include('products.product.include.upload')
                        @else
                            @include('products.product.include.create')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



@section('js')


    <script src="{{ asset('assets/custom_js/file_upload.js') }}"></script>

    <script>
        function barcode_generate(length) {
            var result = '';
            var characters = '0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }
        $('#barcode_generate').on('click', function() {
            $('#product_barcode').val(barcode_generate(5));
        })



        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#blah').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $('.opening_stock').on('keyup', function () {

            let qty = $(this).val();
            if (qty > 0){
                $('.expiry').show()
            }else {
                $('.expiry').hide()
            }
        })

        $(".ace-file-upload").change(function() {
            readURL(this);
        });



        {{--$('#brand_form').on('submit',function(){--}}

        {{--    let name = $('.brand_name').val();--}}

        {{--    if(name){--}}
        {{--        $.ajax({--}}
        {{--            type:"POST",--}}
        {{--            url:"{{ url('dokani/create/brand') }}",--}}
        {{--            data:{--}}
        {{--                name : name,--}}
        {{--                _token: '{{ csrf_token() }}'--}}
        {{--            },--}}
        {{--            success:function(res){--}}
        {{--                if(res){--}}
        {{--                    console.log(res)--}}
        {{--                    // $("#city").empty();--}}
        {{--                    // $("#city").append('<option>Select</option>');--}}
        {{--                    // $.each(res.cities,function(key,value){--}}
        {{--                    //     $("#city").append('<option value="'+value.id+'">'+value.name+'</option>');--}}
        {{--                    // });--}}
        {{--                }else{--}}
        {{--                    // $("#city").empty();--}}
        {{--                }--}}
        {{--            }--}}
        {{--        });--}}
        {{--    }else{--}}
        {{--        // $("#city").empty();--}}
        {{--    }--}}
        {{--});--}}



    </script>
@endsection

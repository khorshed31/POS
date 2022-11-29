<script>
    $(document).ready(function() {

        $(document).on('click', '.pagination a', function(event) {
            event.preventDefault()

            $('li').removeClass('active')
            $(this).parent('li').addClass('active')

            var myurl = $(this).attr('href')
            var page = $(this).attr('href').split('page=')[1]

            loadData(page)
        })

    })

    function loadData(page) {

        let category_id = $('#category option:selected').val()

        $.ajax({
            url: '/dokani/product/get-purchase-products',

            data: {
                page: page,
                category_id: category_id
            },
            type: "get",
            datatype: "html"
        }).done(function(data) {
            $(".product-list1").empty().html(data)
            location.hash = page
        }).fail(function(jqXHR, ajaxOptions, thrownError) {
            alert('No response from server')
        })
    }
</script>

<!-- Product Search Ajax -->
<script>
    function getProductByCategory(obj) {

        axios({
            method: 'get',
            url: "/dokani/product/get-purchase-products?category_id=" + $(obj).val(),

        }).then(function(response) {
            $('.product-list1').html(response.data)
        })
    }

    $(document).on('keyup', ".product-search-input", $.debounce(500, function(e) {
        getProductInfo(this, e)
    }));

    function getProductInfo(params, event) {


        let _this = $(params)
        let value = _this.val()
        event.preventDefault()



        if (event.which != 38 && event.which != 40) {
            //     if (event.which == 17) {
            if (value != '') {


                $.ajax({
                    type:'POST',
                    url: "{{ route('dokani.get-searchable-products-ajax') }}",
                    data: {
                        _token: '{!! csrf_token() !!}',
                        search: value,
                    },
                    // beforeSend: function(){
                    //     $('.ajax-loader').css("visibility", "visible");
                    // },
                    success:function(data) {

                        selectedLiIndex = -1
                        products(data)

                        if (data.length == 1) {
                            AddToRowIfItemOne(data[0], event)
                        }
                    },
                    // complete: function(){
                    //     $('.ajax-loader').css("visibility", "hidden");
                    // }
                });

            } else {
                $('.product-search').html('')
            }

        }
        arrowUpDownInit(event)
    }
</script>





<!-- PRODUCT UI LIST -->
<script>
    function products(data) {

        let table = $('#purchase-table tbody');
        let length = $('#purchase-table tbody tr').length + 1;
        let li = `<ul class="dropdown-menu search-product" role="menu" style="z-index: 99999999">`

        data.map(function(value) {

            li += `<li class="single-product-li dropdown-item" onclick="GetInfo(this)">
            <a href="javascript:void(0)">
                <span class="product-title"> ${value.name}</span>
                <span>(Stock:<span class="product-stock">${value.stock}</span>)</span>
                <span>Sku: <span class="sku-code">${value.product_code}</span></span>
                <span>Price: <span class="product-price">${value.product_cost}</span></span>
                <span class="product-unit" style="display: none">${value.unit}</span>
                <p style="display: none" class="product_id">${value.id}</p>
                <input type="hidden" class="product-stock" value="${value.stock}">
            </a>
        </li>`

            // let barcode = $('.product-search-input').val()
            //
            // if (barcode == value.product_code){
            //     addItem(length, value.id, value.name, value.product_code, 1, value.product_cost, table)
            // }
            // $('.product-search-input').val(null).focus()
        })

        li += '</ul>'


        $('.product-search').html(li)

        var mouse_is_inside = false;

        $(document).ready(function()
        {
            $('.search-product').click(function(){
                mouse_is_inside = true;
            }, function(){
                mouse_is_inside = false;
            });

            $("body").mouseup(function(){
                if(! mouse_is_inside) $('.search-product').hide();
            });
        });

    }




    function AddToRowIfItemOne(data, event) {
        let length = $('#purchase-table tbody tr').length + 1

        let keycode = (event.keyCode ? event.keyCode : event.which)

        if (keycode == 13) {

            event.preventDefault()

            // if (data?.stock > 0) {

            addItem(length, data?.id, data?.name, data?.product_code, 1, data?.product_price ?? 0,
                $('#purchase-table tbody'))
            // } else {
            //     alert('Product is not have enough stock')
            // }

            $('.product-search').html('')
            // $('#product-' + data?.id).focus()
            $('.product-search-input').val('').focus()
        }

    }
</script>

{{-- UI ELEMENT SELECT USING UP DOWN ARROW KEY --}}
<script>
    let selectedLiIndex = -1
    $(document).bind('keydown', focusDiscountInput)
    $('.product-search-input').focus()

    function focusDiscountInput() {

        if (event.ctrlKey && event.code === "Space") {
            $('#discount').focus()

            event.preventDefault()
        }
    }

    $('#discount').on('keydown', function(event) {

        let keycode = (event.keyCode ? event.keyCode : event.which)

        if (event.ctrlKey && event.code === "Space") {
            $('#paid_amount').focus()

            event.preventDefault()
        }

        if (keycode == 13) {
            $('#paid_amount').focus()
            event.preventDefault()
        }
    })



    function arrowUpDownInit(e) {

        e.preventDefault()

        $('.search-product').find('li').removeClass('background')

        var li = $('.search-product').find('li')

        var selectedItem


        if (e.which === 40) {

            selectedLiIndex += 1

        } else if (e.which === 38) {

            selectedLiIndex -= 1
        }




        if (selectedLiIndex < 0) {
            selectedLiIndex = 0
        }



        if (li.length <= selectedLiIndex) {
            selectedLiIndex = 0
        }

        if (e.which == 40 || e.which == 38) {

            selectedItem = $('.search-product').find(`li:eq(${selectedLiIndex})`).addClass('background')
            select(selectedItem)

        }

        // addItemOnEnter($('.search-product').find(`li:eq(${selectedLiIndex})`), e)
    }
</script>

<script>
    function addItemOnEnter(object, e) {


        if (e.which == 13) {

            let _this = $(object)

            let product_id = _this.find('.product_id').text()
            let product_title = _this.find('.product-title').text()
            let product_code = _this.find('.sku-code').text()
            let product_price = _this.find('.product-price').text()
            let product_vat = _this.find('.product-vat').text()

            let product_stock = _this.find('.product-stock').text()

            let table = $('#purchase-table tbody')
            let length = $('#purchase-table tbody tr').length + 1


            if (product_id != '') {

                // if (product_stock > 0) {

                    // add item into the sale table
                    addItem(length, product_id, product_title.trim(), product_code, 1, product_price.trim(),
                        table)


                    $('.product-search').html('')
                    $('.product-search-input').val('').focus()

                    $('#product-' + product_id)
                    product_id = ''

                // } else {
                //     alert('Product is not have enough stock')
                // }
            }

        }
    }
</script>



<script>
    function select(el, nodes) {

        var ul = $('.search-product')


        var elHeight = $(el).height()
        var scrollTop = ul.scrollTop()
        var viewport = scrollTop + ul.height()
        var elOffset = (elHeight + 10) * selectedLiIndex

        if (elOffset < scrollTop || (elOffset + elHeight) > viewport)
            $(ul).scrollTop(elOffset)

        selectedItem = $('.search-product').find(`li:eq(${selectedLiIndex})`).addClass('background')

    }
</script>



<script>

    function addPurchaseItem(id, product_id, title, code, qty, price,unit, table) {
        let is_item_added = true

        $('.tr_product_id').each(function (index, value) {
            if ($(this).val() == product_id) {
                is_item_added = false
                let closest_tr = $(this).closest('.mgrid')
                Increase($(this))
                return false
            }
        })

        if (is_item_added == true) {

            let tr = `<tr class="mgrid">
                <td style="width:3%">
                    <span class="serial">${id}</span>
                    <input type="hidden" class="tr_product_id" name="product_ids[]" value="${product_id}" />
                </td>
                <td style="width:20%"> ${title}
                <input type="hidden" name="product_titles[]" value="${title}"/>
                </td>
                <td style="width:10%" class="text-left">
                <input type="hidden" name="product_codes[]" value="${code}"/>
                ${code}
                </td>
                @if(optional(auth()->user()->businessProfile)->has_expiry_date == 0)
                    <td style="width:15%" class="text-left">
                         <input type="date" name="expiry_at[]" class="form-control date-picker" id="date" autocomplete="off">
                    </td>
                @endif
                <td style="width:15%" class="text-left">
                        <div class="input-group">

                            <input class="form-control product_qty input-sm" id="product-${product_id}"
                            type="number" onkeydown="focusOnEnter(event,'product-search-input')"
                            onkeyup="updateCart(this,event)" name="product_qty[]" value="${qty}" style="width:123px">

                        </div>
                </td>
                <td style="width:15%">
                    <strong class="">${unit}</strong>
                </td>
                <td style="width:15%">
                    <input type="text" name="product_price[]" class="form-control product-cost input-sm" onkeyup="updateCart(this,event)" value="${price}"
                        autocomplete="off">
                </td>

                <td style="width:15%">
                <input type="hidden" name="subtotal[]" value="${price}"/>
                <strong class="subtotal text-right">${price}</strong>
                </td>
                <td style="width:5%">
                    <a href="#" class="text-danger" onclick="removeField(this)">
                        <i class="far fa-times-circle fa-lg"></i>
                    </a>
                </td>
            </tr>`
            table.append(tr)
            calculate()
        }
    }



    function Decrease(object) {
        let _this = $(object)
        let input = _this.closest('.mgrid').find('.product_qty')
        let qty = input.val()
        if (qty > 1) {
            input.val(Number(qty - 1))
        }
        updateCart(object)
    }

    function Increase(object) {
        let _this = $(object)
        let input = _this.closest('.mgrid').find('.product_qty')
        let qty = input.val()
        input.val(Number(qty) + 1)
        updateCart(object)
    }


    function removeField(object) {
        $(object).closest('.mgrid').remove()
        serial()
        calculate()
    }
    function serial() {
        $('.serial').each(function (index) {
            $(this).text(index + 1)
        })
    }

    function updateCart(object, event) {



        let _this = $(object).closest('.mgrid')
        let qty = _this.find('.product_qty').val()
        let price = _this.find('.product-cost').val()

        if (qty <= 0) {
            qty = 1
            _this.find('.product_qty').val(qty)
        }


        let total = ((Number(qty) * Number(price))).toFixed(2)


        _this.find('.subtotal').text(total)


        calculate()



        // if (event.which == 13) {
        //     $('.product-search-input').focus()

        // }
    }



    function calculate() {
        let price = 0

        $('.subtotal').each(function () {
            price += Number($(this).text())
        })


        // let total       = Number($('#total').val() | 0)
        let pre_due = Number($('#previous_due').val())
        let discount = Number($('#discount').val())
        let grand_total = (price + pre_due) - discount
        let paid_amount = Number($('#paid_amount').val())
        let payable_amount = price + pre_due - discount
        let due_amount = grand_total - paid_amount



        $('#total').val(price)
        // $('.payable_amount').text(grand_total)
        // $('#payable_amount').val(grand_total)
        $('#grand_total').val(grand_total)
        $('.grand_total').text(grand_total)
        $('#due_amount').val(due_amount)
        $('.due_amount').text(due_amount)

    }



    $('#discount,#paid_amount').keyup(function () {

        calculate()
    })



    // $('#paid_amount').keyup(function () {
    //     let _this = $(this)
    //     let paid_amount = Number(_this.val() || 0)
    //     let grand_total = Number($('#grand_total').val() | 0)
    //     $('#due_amount').val(grand_total - paid_amount)

    // })





    function focusOnEnter(event, id) {


        let keycode = (event.keyCode ? event.keyCode : event.which)

        if (keycode == 13) {
            event.preventDefault()
            $('.' + id).focus()
        }
    }
</script>

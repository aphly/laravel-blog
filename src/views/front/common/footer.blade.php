</main>
<footer>
    <div class="footer1">
        <div class="container">
            <div class="footer11">
                <ul>
                    <li>Information</li>
                    <li><a href="{{config('blog.menu.about_us')}}">About Us</a></li>
                    <li><a href="{{config('blog.menu.terms_of_service')}}">Terms of Service</a></li>
                    <li><a href="{{config('blog.menu.privacy_policy')}}">Privacy Policy</a></li>
                    <li><a href="{{config('blog.menu.faq')}}">FAQ</a></li>
                </ul>
                <ul style="margin-right: auto">
                    <li>Support</li>
                    <li><a href="{{config('blog.menu.contact_us')}}">Contact Us</a></li>
                    <li><a href="{{config('blog.menu.payment')}}">Payment</a></li>
                    <li><a href="{{config('blog.menu.shipping')}}">Shipping</a></li>
                    <li><a href="{{config('blog.menu.refund_policy')}}">Refund Policy</a></li>
                </ul>
                <ul>

                </ul>
            </div>
        </div>
    </div>
    <div class="footer2">
        <div class="container">
            <div class="footer21">
                <div class="footer21a">
                    <div>
                        <i class="uni app-world"></i>
                    </div>

                </div>
                <div class="footer21b">
                    © {{date('Y')}} <a href="{{url('')}}">{{config('blog.hostname')}}</a> All Rights Reserved.
                </div>
            </div>
        </div>
    </div>
</footer>
<style>
</style>
<script src="{{ URL::asset('static/statistics/js/statistics.js') }}" data-appid="{{config('blog.statistics_appid')}}" id="statistics"></script>
<script src="{{ URL::asset('static/base/js/bootstrap.bundle.min.js') }}"></script>
<script>
    var aphly_viewerjs = document.querySelectorAll('.aphly_viewer_js');
    if(aphly_viewerjs){
        aphly_viewerjs.forEach(function (item,index) {
            new Viewer(item,{
                url: 'data-original',
                toolbar:false,
                title:false,
                rotatable:false,
                scalable:false,
                keyboard:false,
                filter(image) {
                    if(image.className.indexOf("aphly_viewer") !== -1){
                        return true;
                    }else{
                        return false;
                    }
                }
            });
        })
    }
    function subscribe_res(res,_this) {
        alert_msg(res)
    }
    $(function() {
        $("img.lazy").lazyload({effect : "fadeIn",threshold :50});
    })
</script>
</body>
</html>

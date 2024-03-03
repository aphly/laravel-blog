<div class="top-bar">
    <h5 class="nav-title">{!! $res['breadcrumb'] !!}</h5>
</div>
<div class="imain">
    <form method="post" action="/user_admin/user/{{$res['info']['uuid']}}/verify" class="save_form">
        @csrf
        <div class="">
            <div class="form-group">
                <label for="">邮件校验</label>
                <select name="verified"  class="form-control">
                    <option value="1" @if($res['info']['verified']==1) selected @endif>已校验</option>
                    <option value="0" @if($res['info']['verified']==0) selected @endif>未校验</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            <button class="btn btn-primary" type="submit">保存</button>
        </div>
    </form>
</div>





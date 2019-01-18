AJS.$(function(){
    $(document).on('click', ".delete-thumbnail", function(e){
        var _input = $(this).attr('data-value');
        var _button = $(this).attr('data-button');
        var _preview = $(this).attr('data-preview');

        $(_input).val(0);
        $(_button).attr('disabled', false);
        $(this).attr('disabled', true);

        var span = $("<span class='placeholder'>이미지를 선택하세요</span>");
        $(_preview).empty().append(span);
    });
    $('.upload-thumbnail').on('click', function(e){
        var _this = this;
        var _input = $(this).attr('data-input');
        var _delete = $(this).attr('data-delete');
        var _preview = $(this).attr('data-preview');
        var _value = $(this).attr('data-value');

        if(document.getElementById(_input).files.length == 0){
            alert('업로드할 파일을 선택해주세요.');
            return;
        }

        var formData = new FormData();
        formData.append('attachment', document.getElementById(_input).files[0]);
        $.ajax({
            url: '/attachment/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(){
                $(_this).attr('disabled', true);
            },
            success: function(data){
                if(data.result == 200){
                    var id = data.returns.id,
                        dir = data.returns.dir,
                        ext = data.returns.ext;

                    if((['jpg', 'jpeg', 'png', 'gif']).indexOf(ext) == -1){
                        alert("지원되는 이미지 형식이 아닙니다!");
                        return;
                    }

                    var div = $("<div class='img' style='background-image: url(" + dir + ");'></div>");
                    $(_delete).attr('disabled', false);
                    $(_preview).empty().append(div);
                    $(_value).val(id);
                } else {
                    alert(data.message);
                }
            },
            error: function(request, status, error){
                alert(error);
            },
            complete: function(){
                document.getElementById(_input).value = "";
            }
        });
    });
    $(".upfile").on('change', function(e){
        var _button = $(this).attr('data-button');
        $(_button).attr('disabled', false);
    });
});
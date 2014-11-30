// 轮询间隔
var queryTime = 2000;
(function(time) {
    function poll() {
        // $.ajax({
        //     type: "post",
        //     url: "./action/poll.action.php",
        //     success: function(msg) {

        //     }
        // })
    }
    setInterval(poll, time);
})(queryTime)

// 点击对话框关闭按钮关闭对话框
$(document).on("click", ".dialog-close, .btn-dialog-close", function() {
    $(this).closest('.dialog').addClass('hide');
})

// 双击用户打开对话框
$(".main-panel").on("dblclick", ".contacts li", function() {
    var type = $(this).data("type"),
        userId = $(this).data("userId"),
        userName = $(this).data("userName"),
        newUser = new User({
            type: type,
            userId: userId,
            userName: userName
        });
    if(isExistDialog(userId)) {
        showDialog(userId);
    } else {
        createDraggingDialog(newUser);
    }
})

// 点击未读信息打开对话框
$(".message-list").on("click", "li", function() {
    var userId = $(this).data("userId"),
        user = removeMessage(userId);
    createDraggingDialog(user);
})

/**
 *  @function   判断某对话框是否已打开
 *  @param      对话框的聊天对象的userId
 *  @return     true: 存在该对话框    false: 不存在
 *  @author     panda
 *  @version    14.10.01.0
 */
function isExistDialog(userId) {
    if($(".dialog[data-userId=" + userId + "]")) {
        return true;
    }
    return false;
}


/**
 *  @function   在某用户聊天窗口打开的情况下，新增消息
 *  @param      userId  用户userId
 *              msg     要新增的消息, Message对象
 *              align   文本对齐方式    0 表示是对方发来的消息，文本向左对齐（默认）
 *                                      1 表示是自己发出的消息，文本向右对齐
 *  @author     panda
 *  @version    14.10.02.0
 */
function appendMsgToDialog(userId, msg, align) {
    var align = align ? "align-left" : "align-right",
        dialogContent = $(".dialog[data-userId=" + userId + "]").find(".dialog-content"),
        newContent =   '<div class="dialog-msg' + align + '"> \
                            <div class="dialog-msg-title"> \
                                <span class="dislog-msg-name">' + msg.name + '</span> \
                                <span class="dislog-msg-time">' + msg.time + '</span> \
                            </div> \
                            <div class="dialog-msg-content"> \
                                <p>' + msg.msg + '</p> \
                            </div> \
                        </div>' ;
    dialogContent.append(newContent);
}

/**
 *  @function   显示隐藏的对话框（默认对话框存在），并居中
 *  @param      要显示的对话框的userId
 *  @author     panda
 *  @version    14.10.01.0
 */ 
function showDialog(userId) {
    var dialogNode = $(".dialog[data-userId=" + userId + "]");
    dialogNode.removeClass('hide');
    setDialogCenter(dialogNode);
}

// 将对话框居中（默认对话框存在）
function setDialogCenter(dialogNode) {
    var offsetTop = ( $(window).height() - dialogNode.height() ) / 2,
        offsetLeft = ( $(window).width() - dialogNode.width() ) / 2;
    dialogNode.css({
        "top": offsetTop,
        "left": offsetLeft
    });
}

/**
 *  @function   点击未读信息弹出聊天框
 *  @param      User对象（包含某用户未读信息）
 *  @author     panda
 *  @version    14.09.28.1
 */
function createDraggingDialog(user) {
    var newDialogNode = $("#dialog-model").clone(true).removeAttr('id').data('userId', user.userId).find('selector')
    newDialogNode.appendTo('body');
    showDialog(newDialogNode);
}
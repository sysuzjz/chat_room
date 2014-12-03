
// 包含各未读信息
var messageList = Array();

/**
 *  @function    构造器，某条信息
 *  @param       json字符串格式
 *  @author      panda
 *  @version     14.09.28.0
 */
function Message(newMessage) {
    newMessage = JSON.parse(newMessage);
    this.type = newMessage.type; // 0: personal chat    1: group chat
    this.userId = newMessage.userId;
    this.userName = newMessage.userName;
    this.time = newMessage.time;
    this.msg = newMessage.msg;
}

/**
 *  @function   某用户所有信息，构造器
 *  @param      Message格式（对象）
 *  @author     panda
 *  @version    14.09.28.0
 */
function User(message) {
    this.userName = message.userName;
    this.type = message.type;
    this.userId = message.userId;
    this.msgs = Array(message);
    this.addMsg = function(msg) {
        this.msgs.push(msg);
    }
    this.clear = function() {
        while(this.msgs.length) {
            this.msgs.pop();
        }
    }
}

/**
 *  @function   添加未读信息，若messageList已包含该用户未读信息，则添加到此用户未读信息列表中；否则添加新的用户未读信息到messageList
 *  @param      message对象
 *  @author     panda
 *  @version    14.09.28.0
 */
function addMessage(message) {
    for(var i = 0, length = messageList.length; i < length; i++) {
        if(messageList[i].userId == message.userId) {
            messageList[i].addMsg(message);
            return;
        }
    }
    messageList.push(new User(message));
}

/**
 *  @function   点击某用户未读信息后将其从messageList中删除
 *  @param      userId, string类型
 *  @return     被移除的User对象
 *  @author     panda
 *  @version    14.10.02.1
 */
function removeMessage(userId) {
    for(var i = 0, length = messageList.length; i < length; i++) {
        if(messageList[i].userId == userId) {
            var temp = messageList[i];
            messageList.splice(i, 1);
            return temp;
        }
    }
}


/**
 *  @function   判断某个会话是否已存在消息列表中
 *  @param      userId, string类型
 *  @return     true: 在message-list中找到该用户会话     false: 没找到
 *  @author     panda
 *  @vervion    14.10.02.0
 */
function isInMsgList(userId) {
    for(var i = 0, length = messageList.length; i < length; i++) {
        if(messageList[i].userId == userId) {
            return true;
        }
    }
    return false;
}

function updateMsgList(userId) {
    
}
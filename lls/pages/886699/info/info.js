// pages/info/info.js
Page({

    /**
     * 页面的初始数据
     */
    data: {
        data: ''
    },

    /**
     * 生命周期函数--监听页面加载
     */
    oid: '',
    onLoad: function (options) {
        this.oid = options.oid
        this.onLoadData()
    },

    onLoadLocalData(){
        var that = this
        wx.showLoading({ title: '加载中' })
        wx.getStorage({
            key: 'llsinfo_' + that.oid,
            success(res) {
                if (res.data) {
                    that.setData({ data: res.data })
                    wx.hideLoading()
                }
            },
            complete(res) {
                that.onLoadData()
            }
        })
    },

    onLoadData() {
        if (this.oid) {
            const that = this
            wx.request({
                url: 'https://m.ayioz.com/llsinfo.php?oid=' + this.oid,
                header: {
                    'content-type': 'application/json'
                },
                success(res) {
                    if (res.data) {
                        that.setData({ data: res.data })
                        wx.setStorage({
                            key: "llsinfo_" + that.oid,
                            data: res.data
                        })
                    }
                },
                complete() {
                    wx.hideLoading()
                }
            })
        }
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {
        wx.showLoading({ title: '加载中' })
        this.onLoadData()
        wx.stopPullDownRefresh()
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    }
})
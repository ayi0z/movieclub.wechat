// pages/todnew/todnew.js
Page({

    /**
     * 页面的初始数据
     */
    data: {
        today: [],
        yestoday: []
    },

    todayDate:'',
    yestDate:'',
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        const util = require("../../../utils/util")
        const now = new Date()
        this.todayDate = util.formatDate(now)
        now.setTime(now.getTime() - 24 * 3600 * 1000)
        this.yestDate = util.formatDate(now)
        this.onLocalLoadData()
    },

    onLocalLoadData() {
        var that = this
        wx.showLoading({ title: '加载中' })
        wx.getStorage({
            key: 'llstodnew',
            success(res) {
                if (res.data && res.data.length > 0) {
                    var todays = res.data.filter(c => c.newtoday === that.todayDate)
                    var yestodays = res.data.filter(c => c.newtoday === that.yestDate)
                    that.setData({ today: todays, yestoday: yestodays })
                    wx.hideLoading()
                }
            },
            complete(res) {
                that.onLoadData()
            }
        })
    },

    onLoadData() {
        var that = this
        wx.request({
            url: 'https://m.ayioz.com/llstodnew.php',
            header: {
                'content-type': 'application/json'
            },
            success(res) {
                if (res.data.data && res.data.data.length > 0) {
                    var todays = res.data.data.filter(c => c.newtoday === that.todayDate)
                    var yestodays = res.data.data.filter(c => c.newtoday === that.yestDate)
                    that.setData({ today: todays, yestoday: yestodays })

                    wx.setStorage({
                        key: "llstodnew",
                        data: res.data.data
                    })
                }
            },
            complete() {
                wx.hideLoading()
                wx.stopPullDownRefresh()
            }
        })
    },

    onShowInfo: function(e){
        if(e.currentTarget.dataset.oid){
            wx.navigateTo({url: '/pages/886699/info/info?oid='+e.currentTarget.dataset.oid})
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
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    }
})
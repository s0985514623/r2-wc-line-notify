import jQuery from 'jquery'
import '@/assets/scss/index.scss'
;(function ($) {
  const textarea = $('#r2_wc_line_notify_message')

  // 監聽點擊事件

  $(document).on('click', '.shortcode-code', function (e) {
    e.preventDefault() // 防止點擊<a>標籤時的預設動作
    const textToInsert = $(this).text() // 獲取<a>標籤的文字
    const cursorPos = textarea.prop('selectionStart') // 獲取當前游標位置
    const v = textarea.val() as string // 獲取<textarea>的當前值
    const textBefore = v.substring(0, cursorPos) // 當前游標前的文字
    const textAfter = v.substring(cursorPos, v.length) // 當前游標後的文字

    // 將文字插入游標位置並更新<textarea>的值

    textarea.val(textBefore + textToInsert + textAfter)
  })
})(jQuery)

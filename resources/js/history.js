import HistoryManager from './helpers/history'

document.addEventListener('DOMContentLoaded', () => {
    historyManager.pushPage(document.title)
})

window.historyManager = new HistoryManager()

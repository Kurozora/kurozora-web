import Tool from './pages/Tool'

Nova.booting((app, store) => {
  Nova.inertia('{{ class }}', Tool)
})

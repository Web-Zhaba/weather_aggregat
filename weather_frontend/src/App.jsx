import { Route, Routes } from 'react-router-dom'
import MainLayout from './components/MainLayout'
import ProtectedRoute from './components/ProtectedRoute'
import HomePage from './pages/HomePage'
import LoginPage from './pages/LoginPage'
import RegistrationPage from './pages/RegistrationPage'
import MainPage from './pages/MainPage'
import NotFoundPage from './pages/NotFoundPage'
import FavoritesPage from './pages/FavoritesPage'

function App() {
  return (
    <Routes>
      <Route element={<MainLayout />}>
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegistrationPage />} />
        <Route path="/main" element={<MainPage />} />
        
        <Route element={<ProtectedRoute />}>
          <Route path="/favorites" element={<FavoritesPage />} />
        </Route>
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  )
}

export default App

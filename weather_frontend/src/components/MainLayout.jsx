import { Outlet, Link, NavLink } from "react-router-dom";
import styles from './MainLayout.module.css'
import Footer from "./Footer";

export default function MainLayout() {
    return(
        <div className={styles['main-layout']}>
            <header className={styles['header']}>
                <nav className={styles['nav']}>
                    <Link to="/" className={styles['logo']}>Weather Aggregator</Link>

                    <NavLink to="/main" className={styles['nav-link']}>Главная</NavLink>
                    <NavLink to="/login" className={styles['nav-link']}>Вход</NavLink>
                    <NavLink to="/register" className={styles['nav-link']}>Регистрация</NavLink>
                </nav>
            </header>
            <main>
                <Outlet />
            </main>
            <Footer />
        </div>
    );
}
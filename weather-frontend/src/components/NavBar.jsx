import { Link, NavLink } from "react-router-dom";

export default function NavBar() {
    return (
        <nav>
            <Link to="/">Weather Aggregator</Link>

            <NavLink to="/main">Главная</NavLink>
            <NavLink to="/main">Вход</NavLink>
            <NavLink to="/main">Регистрация</NavLink>
        </nav>
    );
}
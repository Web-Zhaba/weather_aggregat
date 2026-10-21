import { Link } from "react-router-dom";

export default function NotFoundPage() {
  return (
    <>
    <h1>404 - Page Not Found</h1>
    <h2>Страница не найдена. Перейти на домашнюю страницу:</h2>
    <Link to="/">Домашняя страница</Link>
    </>
  );
}
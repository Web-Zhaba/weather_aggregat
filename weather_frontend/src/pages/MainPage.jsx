import useLocalStorage from "../hooks/useLocalStorage.js";

export default function MainPage() {
  const [city, setCity] = useLocalStorage("city");
  const [date, setDate] = useLocalStorage("date");
  
  function handleSubmit(event) {
    event.preventDefault();
    alert(`Поиск погоды для города: ${city} на дату: ${date}`);
  };

  return (
    <div>
      <section className="main-page">
        <h1>Введите город</h1>
        <form onSubmit={handleSubmit}>
          <label>
            Город:
            <input type="text" name="city" value={city} required placeholder="Название города" onChange={(e) => setCity(e.target.value)} />
          </label>
          <label>
            Дата:
            <input type="date" name="date" value={date} required onChange={(e) => setDate(e.target.value)} />
          </label>
          <button type="submit" name="search">Поиск</button>
        </form>
      </section>
    </div>
  );
}
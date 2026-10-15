import { Outlet } from "react-router-dom";
import NavBar from "./NavBar";
import Footer from "./Footer";

export default function MainLayout() {
    return(
        <div className="">
            <header>
                <NavBar />
            </header>
            <main>
                <Outlet />
            </main>
            <Footer />
        </div>
    );
}
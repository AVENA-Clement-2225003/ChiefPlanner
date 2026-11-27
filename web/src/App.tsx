import { Navigate, Route, Routes } from 'react-router-dom';
import { AppLayout } from '@/components/layout/AppLayout';
import { useCurrentUser } from '@/auth/AuthProvider';
import { AdminPage } from '@/pages/AdminPage';
import { AllDishesPage } from '@/pages/AllDishesPage';
import { DishesPage } from '@/pages/DishesPage';
import { HomePage } from '@/pages/HomePage';
import { StorePage } from '@/pages/StorePage';
import { MealFlowsPage } from '@/pages/MealFlowsPage';
import { ROUTES } from '@/routes';

function AdminOnly() {
  const user = useCurrentUser();
  return user.isAdmin ? <AdminPage /> : <Navigate to={ROUTES.home} replace />;
}

export function App() {
  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route path={ROUTES.home} element={<HomePage />} />
        <Route path={ROUTES.dishes} element={<DishesPage />} />
        <Route path={ROUTES.store} element={<StorePage />} />
        <Route path={ROUTES.mealFlows} element={<MealFlowsPage />} />
        <Route path={ROUTES.allDishes} element={<AllDishesPage />} />
        <Route path={ROUTES.admin} element={<AdminOnly />} />
        <Route path="*" element={<Navigate to={ROUTES.home} replace />} />
      </Route>
    </Routes>
  );
}

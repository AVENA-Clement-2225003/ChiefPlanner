import { Navigate, Route, Routes } from 'react-router-dom';
import { AppLayout } from '@/components/layout/AppLayout';
import { AllDishesPage } from '@/pages/AllDishesPage';
import { DishesPage } from '@/pages/DishesPage';
import { HomePage } from '@/pages/HomePage';
import { IngredientsPage } from '@/pages/IngredientsPage';
import { MealFlowsPage } from '@/pages/MealFlowsPage';
import { ROUTES } from '@/routes';

export function App() {
  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route path={ROUTES.home} element={<HomePage />} />
        <Route path={ROUTES.dishes} element={<DishesPage />} />
        <Route path={ROUTES.ingredients} element={<IngredientsPage />} />
        <Route path={ROUTES.mealFlows} element={<MealFlowsPage />} />
        <Route path={ROUTES.allDishes} element={<AllDishesPage />} />
        <Route path="*" element={<Navigate to={ROUTES.home} replace />} />
      </Route>
    </Routes>
  );
}

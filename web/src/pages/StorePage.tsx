import { useState } from 'react';
import { App as AntdApp, Button, Card, InputNumber, Select, Spin } from 'antd';
import { CloseIcon, PlusIcon } from '@/components/icons';
import { useAsyncData } from '@/hooks/useAsyncData';
import { apiErrorMessage } from '@/services/apiClient';
import { listAisles, listUnits } from '@/services/catalogService';
import { listStoreIngredients, saveStoreIngredient } from '@/services/storeService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { Aisle, StoreIngredient, StorePack, Unit } from '@/types';

interface StoreRowProps {
  ingredient: StoreIngredient;
  aisles: Aisle[];
  units: Unit[];
  onSaved: () => void;
}

function StoreRow({ ingredient, aisles, units, onSaved }: StoreRowProps) {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();
  const [aisleId, setAisleId] = useState<string | null>(ingredient.aisleId);
  const [packs, setPacks] = useState<StorePack[]>(ingredient.packs);
  const [saving, setSaving] = useState(false);

  const patchPack = (index: number, changes: Partial<StorePack>) =>
    setPacks(packs.map((pack, position) => (position === index ? { ...pack, ...changes } : pack)));

  const handleSave = async () => {
    setSaving(true);
    try {
      await saveStoreIngredient(ingredient.ingredientId, aisleId, packs);
      message.success(`${ingredient.name} enregistré`);
      onSaved();
    } catch (error) {
      message.error(apiErrorMessage(error, "Le réglage n'a pas pu être enregistré."));
    } finally {
      setSaving(false);
    }
  };

  return (
    <Card style={{ borderRadius: RADII.card, borderColor: neutrals.border }} styles={{ body: { padding: 16 } }}>
      <div style={{ display: 'flex', gap: 16, alignItems: 'flex-start', flexWrap: 'wrap' }}>
        <span style={{ width: 180, fontWeight: 600, color: neutrals.textPrimary }}>{ingredient.name}</span>

        <Select
          allowClear
          placeholder="Rayon"
          style={{ width: 190 }}
          value={aisleId ?? undefined}
          options={aisles.map((aisle) => ({ value: aisle.id, label: aisle.label }))}
          onChange={(value) => setAisleId(value ?? null)}
        />

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, flex: 1, minWidth: 320 }}>
          {packs.map((pack, index) => (
            <div key={index} style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
              <InputNumber
                min={0}
                style={{ width: 90 }}
                value={pack.size}
                aria-label="Quantité du conditionnement"
                onChange={(value) => patchPack(index, { size: value ?? 0 })}
              />
              <Select
                style={{ width: 130 }}
                value={pack.unitId}
                options={units.map((unit) => ({ value: unit.id, label: unit.label }))}
                onChange={(unitId) => patchPack(index, { unitId })}
              />
              <InputNumber
                min={0}
                style={{ width: 100 }}
                value={pack.price ?? undefined}
                placeholder="Prix"
                suffix="€"
                onChange={(value) => patchPack(index, { price: value })}
              />
              <Button
                type="text"
                aria-label="Retirer ce conditionnement"
                icon={<CloseIcon size={14} />}
                onClick={() => setPacks(packs.filter((_, position) => position !== index))}
              />
            </div>
          ))}
          <Button
            type="link"
            icon={<PlusIcon size={14} />}
            style={{ alignSelf: 'flex-start', padding: 0 }}
            onClick={() => setPacks([...packs, { unitId: units[0]?.id ?? 'unit', size: 1, price: null }])}
          >
            Ajouter un conditionnement
          </Button>
        </div>

        <Button type="primary" loading={saving} disabled={packs.some((pack) => pack.size <= 0)} onClick={handleSave}>
          Enregistrer
        </Button>
      </div>
    </Card>
  );
}

/** "Mon magasin": for each ingredient the user cooks with, its aisle, the packs their store sells and the prices. */
export function StorePage() {
  const { neutrals } = useAppTheme();
  const ingredients = useAsyncData(listStoreIngredients, []);
  const aisles = useAsyncData(listAisles, []);
  const units = useAsyncData(listUnits, []);

  const { data: ingredientRows } = ingredients;
  const { data: aisleRows } = aisles;
  const { data: unitRows } = units;
  if (!ingredientRows || !aisleRows || !unitRows) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 28, fontWeight: 600, color: neutrals.textPrimary }}>
        Mon magasin
      </span>
      <span style={{ fontSize: 14, color: neutrals.textSecondary }}>
        Indiquez comment votre magasin vend vos ingrédients (par exemple gnocchis en 2 ou 3 portions, nuggets par 10 ou
        20). La liste de courses calcule alors les paquets à acheter, et la proposition de la semaine essaie de ne rien
        gaspiller. Les prix sont facultatifs.
      </span>

      {ingredientRows.length === 0 && (
        <span style={{ color: neutrals.textSecondary }}>Créez d&apos;abord des plats : leurs ingrédients apparaîtront ici.</span>
      )}
      {ingredientRows.map((ingredient) => (
        <StoreRow
          key={ingredient.ingredientId}
          ingredient={ingredient}
          aisles={aisleRows}
          units={unitRows}
          onSaved={ingredients.reload}
        />
      ))}
    </div>
  );
}

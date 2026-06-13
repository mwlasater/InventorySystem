<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $search = $request->string('q')->toString();

        $items = Item::active()
            ->where(fn (Builder $query) => $this->applySearch($query, $search))
            ->with(['category', 'tags'])
            ->limit(25)
            ->get();

        return ItemResource::collection($items);
    }

    /**
     * Use MySQL FULLTEXT in production; fall back to LIKE on SQLite (tests) and
     * any other driver without a fulltext index on items.
     */
    private function applySearch(Builder $query, string $search): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $query->whereRaw('MATCH(name, description, notes) AGAINST(? IN BOOLEAN MODE)', [$search.'*']);
        } else {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $query->orWhere('barcode', $search)
            ->orWhere('sku', $search)
            ->orWhere('brand', 'like', "%{$search}%")
            ->orWhere('model_number', 'like', "%{$search}%");
    }
}

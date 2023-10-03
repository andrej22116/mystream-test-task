<?php

namespace App\Orchid\Screens\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProductListScreen extends Screen
{
    protected string $name = 'Products';
    protected string $description = 'List of products';

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'products' => Product::query()->paginate(20)
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('New product')
                ->modal('create_product_modal')
                ->method('createOrUpdate')
                ->icon('plus')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::modal('create_product_modal', [
                Layout::rows([
                    Input::make('id')->hidden(),

                    Input::make('title')
                        ->title('Title')
                        ->required(),

                    Input::make('description')
                        ->title('Description')
                        ->required(),

                    Input::make('price')
                        ->title('Price')
                        ->type('number')
                        ->required(),

                    Select::make('payment')
                        ->title('Payment model')
                        ->options([
                            Product\PaymentType::Monthly->value => Product\PaymentType::Monthly->name,
                            Product\PaymentType::Once->value => Product\PaymentType::Once->name,
                        ])
                        ->required(),
                ])
            ])->async('asyncCreateOrUpdateProduct'),

            Layout::table('products', [
                TD::make('title'),

                TD::make('price')->render(function (Product $product) {
                    return '$' . $product->price;
                }),

                TD::make('payment')->render(function (Product $product) {
                    return match ($product->payment) {
                        Product\PaymentType::Monthly => Product\PaymentType::Monthly->name,
                        Product\PaymentType::Once => Product\PaymentType::Once->name,
                    };
                }),

                TD::make('')->render(function (Product $product) {
                    return Group::make([
                        ModalToggle::make('Edit')
                            ->modal('create_product_modal')
                            ->method('createOrUpdate')
                            ->asyncParameters(['id' => $product->id]),

                        Button::make('Remove')
                            ->method('remove', ['id' => $product->id]),
                    ]);
                })
            ])
        ];
    }

    /**
     * Remove the item
     * @param int $id
     * @return void
     */
    public function remove(int $id): void
    {
        Product::query()->where('id', $id)->delete();
    }

    /**
     * Create the item or update it
     * @param Request $request
     * @return void
     */
    public function createOrUpdate(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|numeric',
            'title' => 'required|max:128',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'payment' => [new Enum(Product\PaymentType::class)],
        ]);

        Product::updateOrCreate(['id' => $data['id'] ?? null], $data);

        Toast::success('Product was created!');
    }

    public function asyncCreateOrUpdateProduct(null|int $id = null): array
    {
        $product = Product::query()->where('id', $id)->first();

        return [
            'id' => $product?->id,
            'title' => $product?->title,
            'description' => $product?->description,
            'price' => $product?->price,
            'payment' => $product?->payment?->value,
        ];
    }
}

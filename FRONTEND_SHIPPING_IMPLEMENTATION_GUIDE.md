# Frontend Implementation Guide - Shipping System

## Overview

This guide provides everything your frontend needs to implement the complete shipping system, including custom per-product shipping, checkout flow, and admin management.

---

## Table of Contents

1. [API Endpoints Reference](#api-endpoints-reference)
2. [Data Models & Types](#data-models--types)
3. [Customer-Facing Features](#customer-facing-features)
4. [Admin Features](#admin-features)
5. [UI Components Needed](#ui-components-needed)
6. [Implementation Steps](#implementation-steps)
7. [Example API Calls](#example-api-calls)
8. [Validation Rules](#validation-rules)

---

## API Endpoints Reference

### Customer APIs

#### 1. Get Available Shipping Methods
```
POST /api/checkout/shipping-methods
```

**Request:**
```typescript
{
  items: Array<{
    product_id: number;
    variation_id?: number;
    quantity: number;
  }>;
  address_id: number;
  subtotal: number;
}
```

**Response:**
```typescript
{
  success: boolean;
  data: {
    [methodCode: string]: {
      code: string;
      name: string;
      description: string;
      cost: number;
      base_shipping_cost?: number;
      custom_shipping_cost?: number;
      delivery_time: string | null;
      free_shipping_min_order: number;
      is_free: boolean;
    }
  }
}
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "pathao_courier": {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service",
      "cost": 95.00,
      "base_shipping_cost": 80.00,
      "custom_shipping_cost": 15.00,
      "delivery_time": "1-2 business days",
      "free_shipping_min_order": 5000.00,
      "is_free": false
    },
    "shah_sports_team": {
      "code": "shah_sports_team",
      "name": "Shah Sports Team Delivery",
      "description": "Our own delivery team",
      "cost": 150.00,
      "base_shipping_cost": 140.00,
      "custom_shipping_cost": 10.00,
      "delivery_time": "2-3 business days",
      "free_shipping_min_order": 10000.00,
      "is_free": false
    }
  }
}
```

**Special Case - All Custom Shipping:**
```json
{
  "success": true,
  "data": {
    "custom": {
      "code": "custom",
      "name": "Custom Shipping",
      "description": "Product-specific shipping rates",
      "cost": 25.00,
      "delivery_time": null,
      "free_shipping_min_order": 0,
      "is_free": false
    }
  }
}
```

#### 2. Preview Order
```
POST /api/checkout/preview
```

**Request:**
```typescript
{
  items: Array<{
    product_id: number;
    variation_id?: number;
    quantity: number;
    price: number;
    is_preorder?: boolean;
  }>;
  shipping_address_id?: number;
  shipping_method?: string;
  coupon_code?: string;
  is_preorder?: boolean;
}
```

**Response:**
```typescript
{
  success: boolean;
  data: {
    subtotal: number;
    shipping_cost: number;
    coupon_discount: number;
    total: number;
    deposit_amount: number | null;
    payable_now: number;
  }
}
```

#### 3. Process Checkout
```
POST /api/checkout/process
```

**Request:**
```typescript
{
  items: Array<{
    product_id: number;
    variation_id?: number;
    quantity: number;
    price: number;
    is_preorder?: boolean;
  }>;
  shipping_address_id: number;
  billing_address_id?: number;
  shipping_method: 'shah_sports_team' | 'pathao_courier' | 'custom';
  payment_method: 'ssl_commerz' | 'bkash' | 'nagad';
  coupon_code?: string;
  notes?: string;
  is_preorder?: boolean;
}
```

**Response:**
```typescript
{
  success: boolean;
  message: string;
  data: {
    order: Order;
    payment_url?: string;
  }
}
```

#### 4. Track Order
```
GET /api/orders/{orderNumber}/track
```

**Response:**
```typescript
{
  success: boolean;
  data: {
    order_number: string;
    status: string;
    shipping_method: string;
    tracking_number: string | null;
    created_at: string;
    updated_at: string;
  }
}
```

### Admin APIs

#### 1. Shipping Rates Management

**List Rates:**
```
GET /api/admin/shipping-rates?method={method}&is_active={boolean}&per_page={number}
```

**Create Rate:**
```
POST /api/admin/shipping-rates
```
```typescript
{
  name: string;
  method: 'shah_sports_team' | 'pathao_courier';
  shipping_class_id?: number;
  zone?: string;
  base_cost: number;
  per_kg_cost?: number;
  min_weight?: number;
  max_weight?: number;
  free_shipping_threshold?: number;
  is_active?: boolean;
}
```

**Update Rate:**
```
PUT /api/admin/shipping-rates/{id}
```

**Delete Rate:**
```
DELETE /api/admin/shipping-rates/{id}
```

#### 2. Shipping Classes Management

**List Classes:**
```
GET /api/admin/shipping-classes
```

**Create Class:**
```
POST /api/admin/shipping-classes
```
```typescript
{
  name: string;
  description?: string;
}
```

**Update Class:**
```
PUT /api/admin/shipping-classes/{id}
```

**Delete Class:**
```
DELETE /api/admin/shipping-classes/{id}
```

#### 3. Product Management (with Shipping)

**Create Product:**
```
POST /api/admin/products
```
```typescript
{
  name: string;
  category_id: number;
  price: number;
  // ... other product fields
  
  // Shipping fields
  shipping_type?: 'default' | 'free' | 'fixed' | 'per_item';
  shipping_cost?: number;
  requires_shipping?: boolean;
  separate_shipping?: boolean;
  shipping_notes?: string;
  shipping_class_id?: number;
  
  // Variations with shipping
  variations?: Array<{
    sku?: string;
    price?: number;
    quantity?: number;
    shipping_type?: 'inherit' | 'free' | 'fixed' | 'per_item';
    shipping_cost?: number;
    attributes: Record<string, string>;
  }>;
}
```

**Update Product:**
```
PUT /api/admin/products/{id}
```

**Update Variation:**
```
PUT /api/admin/products/{productId}/variations/{variationId}
```
```typescript
{
  price?: number;
  quantity?: number;
  shipping_type?: 'inherit' | 'free' | 'fixed' | 'per_item';
  shipping_cost?: number;
}
```

---

## Data Models & Types

### TypeScript Interfaces

```typescript
// Shipping Method
interface ShippingMethod {
  code: string;
  name: string;
  description: string;
  cost: number;
  base_shipping_cost?: number;
  custom_shipping_cost?: number;
  delivery_time: string | null;
  free_shipping_min_order: number;
  is_free: boolean;
}

// Product Shipping Configuration
interface ProductShipping {
  shipping_type: 'default' | 'free' | 'fixed' | 'per_item';
  shipping_cost: number | null;
  requires_shipping: boolean;
  separate_shipping: boolean;
  shipping_notes: string | null;
  shipping_class_id: number | null;
}

// Variation Shipping Configuration
interface VariationShipping {
  shipping_type: 'inherit' | 'free' | 'fixed' | 'per_item';
  shipping_cost: number | null;
}

// Shipping Rate
interface ShippingRate {
  id: number;
  name: string;
  method: 'shah_sports_team' | 'pathao_courier';
  shipping_class_id: number | null;
  zone: string | null;
  base_cost: number;
  per_kg_cost: number | null;
  min_weight: number | null;
  max_weight: number | null;
  free_shipping_threshold: number | null;
  delivery_time: string | null;
  is_active: boolean;
}

// Shipping Class
interface ShippingClass {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  products_count: number;
}

// Cart Item
interface CartItem {
  product_id: number;
  variation_id?: number;
  quantity: number;
  price: number;
  name: string;
  image?: string;
  shipping_type?: string;
  shipping_cost?: number;
  requires_shipping?: boolean;
}

// Order Preview
interface OrderPreview {
  subtotal: number;
  shipping_cost: number;
  coupon_discount: number;
  total: number;
  deposit_amount: number | null;
  payable_now: number;
}
```

---


## Customer-Facing Features

### 1. Product Display

**Show Shipping Information on Product Page:**

```tsx
// Product Card/Detail Component
interface ProductDisplayProps {
  product: {
    id: number;
    name: string;
    price: number;
    shipping_type: string;
    shipping_cost: number | null;
    requires_shipping: boolean;
    shipping_notes: string | null;
  };
}

function ProductShippingBadge({ product }: ProductDisplayProps) {
  if (!product.requires_shipping) {
    return <Badge color="blue">Digital Product - No Shipping</Badge>;
  }
  
  if (product.shipping_type === 'free') {
    return <Badge color="green">Free Shipping</Badge>;
  }
  
  if (product.shipping_type === 'fixed') {
    return <Badge color="orange">Flat Rate: ${product.shipping_cost}</Badge>;
  }
  
  if (product.shipping_type === 'per_item') {
    return <Badge color="purple">${product.shipping_cost} per item</Badge>;
  }
  
  return <Badge color="gray">Standard Shipping</Badge>;
}
```

**Display Shipping Notes:**
```tsx
{product.shipping_notes && (
  <Alert type="info">
    <Icon name="info" />
    {product.shipping_notes}
  </Alert>
)}
```

### 2. Cart Page

**Calculate Shipping Preview:**

```tsx
function CartShippingPreview({ items, addressId }: CartProps) {
  const [shippingMethods, setShippingMethods] = useState<ShippingMethod[]>([]);
  const [loading, setLoading] = useState(false);
  
  useEffect(() => {
    if (addressId && items.length > 0) {
      fetchShippingMethods();
    }
  }, [items, addressId]);
  
  const fetchShippingMethods = async () => {
    setLoading(true);
    try {
      const response = await api.post('/checkout/shipping-methods', {
        items: items.map(item => ({
          product_id: item.product_id,
          variation_id: item.variation_id,
          quantity: item.quantity
        })),
        address_id: addressId,
        subtotal: calculateSubtotal(items)
      });
      
      setShippingMethods(Object.values(response.data.data));
    } catch (error) {
      console.error('Failed to fetch shipping methods', error);
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <div className="shipping-preview">
      <h3>Shipping Options</h3>
      {loading ? (
        <Spinner />
      ) : (
        <div className="shipping-methods">
          {shippingMethods.map(method => (
            <ShippingMethodCard key={method.code} method={method} />
          ))}
        </div>
      )}
    </div>
  );
}
```

**Shipping Method Card:**
```tsx
function ShippingMethodCard({ method, selected, onSelect }: ShippingMethodCardProps) {
  return (
    <div 
      className={`shipping-method ${selected ? 'selected' : ''}`}
      onClick={() => onSelect(method.code)}
    >
      <div className="method-header">
        <Radio checked={selected} />
        <div>
          <h4>{method.name}</h4>
          <p className="description">{method.description}</p>
        </div>
        <div className="cost">
          {method.is_free ? (
            <span className="free">FREE</span>
          ) : (
            <span className="price">৳{method.cost.toFixed(2)}</span>
          )}
        </div>
      </div>
      
      {method.delivery_time && (
        <p className="delivery-time">
          <Icon name="clock" /> {method.delivery_time}
        </p>
      )}
      
      {method.custom_shipping_cost > 0 && (
        <div className="cost-breakdown">
          <small>Base: ৳{method.base_shipping_cost.toFixed(2)}</small>
          <small>Custom: ৳{method.custom_shipping_cost.toFixed(2)}</small>
        </div>
      )}
      
      {!method.is_free && method.free_shipping_min_order > 0 && (
        <p className="free-shipping-hint">
          Add ৳{(method.free_shipping_min_order - subtotal).toFixed(2)} more for free shipping
        </p>
      )}
    </div>
  );
}
```

### 3. Checkout Flow

**Step 1: Address Selection**
```tsx
function CheckoutAddressStep({ onNext }: CheckoutStepProps) {
  const [addresses, setAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState<number | null>(null);
  
  const handleNext = () => {
    if (selectedAddress) {
      onNext({ addressId: selectedAddress });
    }
  };
  
  return (
    <div className="checkout-step">
      <h2>Shipping Address</h2>
      <AddressList 
        addresses={addresses}
        selected={selectedAddress}
        onSelect={setSelectedAddress}
      />
      <Button onClick={handleNext} disabled={!selectedAddress}>
        Continue to Shipping
      </Button>
    </div>
  );
}
```

**Step 2: Shipping Method Selection**
```tsx
function CheckoutShippingStep({ 
  items, 
  addressId, 
  onNext 
}: CheckoutShippingStepProps) {
  const [methods, setMethods] = useState<ShippingMethod[]>([]);
  const [selected, setSelected] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetchShippingMethods();
  }, []);
  
  const fetchShippingMethods = async () => {
    const response = await api.post('/checkout/shipping-methods', {
      items: items.map(item => ({
        product_id: item.product_id,
        variation_id: item.variation_id,
        quantity: item.quantity
      })),
      address_id: addressId,
      subtotal: calculateSubtotal(items)
    });
    
    setMethods(Object.values(response.data.data));
    setLoading(false);
  };
  
  const handleNext = () => {
    if (selected) {
      const selectedMethod = methods.find(m => m.code === selected);
      onNext({ 
        shippingMethod: selected,
        shippingCost: selectedMethod?.cost || 0
      });
    }
  };
  
  return (
    <div className="checkout-step">
      <h2>Shipping Method</h2>
      {loading ? (
        <Spinner />
      ) : (
        <>
          <div className="shipping-methods">
            {methods.map(method => (
              <ShippingMethodCard
                key={method.code}
                method={method}
                selected={selected === method.code}
                onSelect={setSelected}
              />
            ))}
          </div>
          <Button onClick={handleNext} disabled={!selected}>
            Continue to Payment
          </Button>
        </>
      )}
    </div>
  );
}
```

**Step 3: Order Preview**
```tsx
function CheckoutPreviewStep({ 
  items, 
  addressId, 
  shippingMethod,
  couponCode,
  onConfirm 
}: CheckoutPreviewStepProps) {
  const [preview, setPreview] = useState<OrderPreview | null>(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetchPreview();
  }, [couponCode]);
  
  const fetchPreview = async () => {
    const response = await api.post('/checkout/preview', {
      items: items.map(item => ({
        product_id: item.product_id,
        variation_id: item.variation_id,
        quantity: item.quantity,
        price: item.price
      })),
      shipping_address_id: addressId,
      shipping_method: shippingMethod,
      coupon_code: couponCode
    });
    
    setPreview(response.data.data);
    setLoading(false);
  };
  
  return (
    <div className="checkout-step">
      <h2>Order Summary</h2>
      
      {loading ? (
        <Spinner />
      ) : preview && (
        <div className="order-summary">
          <div className="summary-row">
            <span>Subtotal</span>
            <span>৳{preview.subtotal.toFixed(2)}</span>
          </div>
          
          <div className="summary-row">
            <span>Shipping</span>
            <span>
              {preview.shipping_cost === 0 ? (
                <span className="free">FREE</span>
              ) : (
                `৳${preview.shipping_cost.toFixed(2)}`
              )}
            </span>
          </div>
          
          {preview.coupon_discount > 0 && (
            <div className="summary-row discount">
              <span>Discount</span>
              <span>-৳{preview.coupon_discount.toFixed(2)}</span>
            </div>
          )}
          
          <div className="summary-row total">
            <span>Total</span>
            <span>৳{preview.total.toFixed(2)}</span>
          </div>
          
          {preview.deposit_amount && (
            <div className="summary-row deposit">
              <span>Deposit (Pay Now)</span>
              <span>৳{preview.deposit_amount.toFixed(2)}</span>
            </div>
          )}
        </div>
      )}
      
      <Button onClick={onConfirm} disabled={loading}>
        Place Order
      </Button>
    </div>
  );
}
```

**Step 4: Process Order**
```tsx
async function processCheckout(checkoutData: CheckoutData) {
  try {
    const response = await api.post('/checkout/process', {
      items: checkoutData.items.map(item => ({
        product_id: item.product_id,
        variation_id: item.variation_id,
        quantity: item.quantity,
        price: item.price
      })),
      shipping_address_id: checkoutData.addressId,
      billing_address_id: checkoutData.billingAddressId,
      shipping_method: checkoutData.shippingMethod,
      payment_method: checkoutData.paymentMethod,
      coupon_code: checkoutData.couponCode,
      notes: checkoutData.notes
    });
    
    if (response.data.success) {
      // Redirect to payment or order confirmation
      if (response.data.data.payment_url) {
        window.location.href = response.data.data.payment_url;
      } else {
        router.push(`/orders/${response.data.data.order.order_number}`);
      }
    }
  } catch (error) {
    console.error('Checkout failed', error);
    showError('Failed to process order. Please try again.');
  }
}
```

### 4. Order Tracking

```tsx
function OrderTracking({ orderNumber }: OrderTrackingProps) {
  const [tracking, setTracking] = useState(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetchTracking();
  }, [orderNumber]);
  
  const fetchTracking = async () => {
    const response = await api.get(`/orders/${orderNumber}/track`);
    setTracking(response.data.data);
    setLoading(false);
  };
  
  if (loading) return <Spinner />;
  
  return (
    <div className="order-tracking">
      <h2>Order Tracking</h2>
      <div className="tracking-info">
        <p><strong>Order Number:</strong> {tracking.order_number}</p>
        <p><strong>Status:</strong> <StatusBadge status={tracking.status} /></p>
        <p><strong>Shipping Method:</strong> {tracking.shipping_method}</p>
        {tracking.tracking_number && (
          <p><strong>Tracking Number:</strong> {tracking.tracking_number}</p>
        )}
      </div>
      
      <OrderTimeline 
        createdAt={tracking.created_at}
        updatedAt={tracking.updated_at}
        status={tracking.status}
      />
    </div>
  );
}
```

---


## Admin Features

### 1. Shipping Rates Management

**List Shipping Rates:**
```tsx
function ShippingRatesList() {
  const [rates, setRates] = useState<ShippingRate[]>([]);
  const [filters, setFilters] = useState({
    method: '',
    is_active: ''
  });
  const [pagination, setPagination] = useState({ page: 1, perPage: 15 });
  
  useEffect(() => {
    fetchRates();
  }, [filters, pagination]);
  
  const fetchRates = async () => {
    const params = new URLSearchParams({
      ...filters,
      page: pagination.page.toString(),
      per_page: pagination.perPage.toString()
    });
    
    const response = await api.get(`/admin/shipping-rates?${params}`);
    setRates(response.data.data.data);
  };
  
  return (
    <div className="shipping-rates-list">
      <div className="header">
        <h1>Shipping Rates</h1>
        <Button onClick={() => openCreateModal()}>Add New Rate</Button>
      </div>
      
      <div className="filters">
        <Select 
          value={filters.method}
          onChange={(value) => setFilters({ ...filters, method: value })}
          placeholder="Filter by method"
        >
          <option value="">All Methods</option>
          <option value="shah_sports_team">Shah Sports Team</option>
          <option value="pathao_courier">Pathao Courier</option>
        </Select>
        
        <Select 
          value={filters.is_active}
          onChange={(value) => setFilters({ ...filters, is_active: value })}
          placeholder="Filter by status"
        >
          <option value="">All Status</option>
          <option value="true">Active</option>
          <option value="false">Inactive</option>
        </Select>
      </div>
      
      <Table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Method</th>
            <th>Zone</th>
            <th>Base Cost</th>
            <th>Free Shipping Threshold</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {rates.map(rate => (
            <tr key={rate.id}>
              <td>{rate.name}</td>
              <td><Badge>{rate.method}</Badge></td>
              <td>{rate.zone || 'All'}</td>
              <td>৳{rate.base_cost.toFixed(2)}</td>
              <td>
                {rate.free_shipping_threshold 
                  ? `৳${rate.free_shipping_threshold.toFixed(2)}`
                  : 'N/A'
                }
              </td>
              <td>
                <StatusBadge active={rate.is_active} />
              </td>
              <td>
                <Button size="sm" onClick={() => editRate(rate)}>Edit</Button>
                <Button size="sm" variant="danger" onClick={() => deleteRate(rate.id)}>
                  Delete
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </Table>
      
      <Pagination 
        current={pagination.page}
        total={rates.length}
        perPage={pagination.perPage}
        onChange={(page) => setPagination({ ...pagination, page })}
      />
    </div>
  );
}
```

**Create/Edit Shipping Rate Form:**
```tsx
function ShippingRateForm({ rate, onSave, onCancel }: ShippingRateFormProps) {
  const [formData, setFormData] = useState({
    name: rate?.name || '',
    method: rate?.method || 'pathao_courier',
    shipping_class_id: rate?.shipping_class_id || null,
    zone: rate?.zone || '',
    base_cost: rate?.base_cost || 0,
    per_kg_cost: rate?.per_kg_cost || 0,
    min_weight: rate?.min_weight || null,
    max_weight: rate?.max_weight || null,
    free_shipping_threshold: rate?.free_shipping_threshold || null,
    is_active: rate?.is_active ?? true
  });
  
  const [errors, setErrors] = useState({});
  
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      const endpoint = rate 
        ? `/admin/shipping-rates/${rate.id}`
        : '/admin/shipping-rates';
      
      const method = rate ? 'PUT' : 'POST';
      
      const response = await api[method.toLowerCase()](endpoint, formData);
      
      if (response.data.success) {
        onSave(response.data.data);
      }
    } catch (error) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      }
    }
  };
  
  return (
    <form onSubmit={handleSubmit} className="shipping-rate-form">
      <h2>{rate ? 'Edit' : 'Create'} Shipping Rate</h2>
      
      <FormField label="Name" required error={errors.name}>
        <Input
          value={formData.name}
          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          placeholder="e.g., Standard Pathao"
        />
      </FormField>
      
      <FormField label="Method" required error={errors.method}>
        <Select
          value={formData.method}
          onChange={(value) => setFormData({ ...formData, method: value })}
        >
          <option value="pathao_courier">Pathao Courier</option>
          <option value="shah_sports_team">Shah Sports Team</option>
        </Select>
      </FormField>
      
      <FormField label="Zone" error={errors.zone}>
        <Input
          value={formData.zone}
          onChange={(e) => setFormData({ ...formData, zone: e.target.value })}
          placeholder="e.g., Dhaka, Chittagong"
        />
      </FormField>
      
      <FormField label="Base Cost" required error={errors.base_cost}>
        <Input
          type="number"
          step="0.01"
          value={formData.base_cost}
          onChange={(e) => setFormData({ ...formData, base_cost: parseFloat(e.target.value) })}
        />
      </FormField>
      
      <FormField label="Per KG Cost" error={errors.per_kg_cost}>
        <Input
          type="number"
          step="0.01"
          value={formData.per_kg_cost}
          onChange={(e) => setFormData({ ...formData, per_kg_cost: parseFloat(e.target.value) })}
        />
      </FormField>
      
      <FormField label="Free Shipping Threshold" error={errors.free_shipping_threshold}>
        <Input
          type="number"
          step="0.01"
          value={formData.free_shipping_threshold || ''}
          onChange={(e) => setFormData({ 
            ...formData, 
            free_shipping_threshold: e.target.value ? parseFloat(e.target.value) : null 
          })}
          placeholder="Order amount for free shipping"
        />
      </FormField>
      
      <FormField label="Active">
        <Checkbox
          checked={formData.is_active}
          onChange={(checked) => setFormData({ ...formData, is_active: checked })}
        />
      </FormField>
      
      <div className="form-actions">
        <Button type="submit">Save</Button>
        <Button type="button" variant="secondary" onClick={onCancel}>
          Cancel
        </Button>
      </div>
    </form>
  );
}
```

### 2. Shipping Classes Management

**List Shipping Classes:**
```tsx
function ShippingClassesList() {
  const [classes, setClasses] = useState<ShippingClass[]>([]);
  
  useEffect(() => {
    fetchClasses();
  }, []);
  
  const fetchClasses = async () => {
    const response = await api.get('/admin/shipping-classes');
    setClasses(response.data.data);
  };
  
  return (
    <div className="shipping-classes-list">
      <div className="header">
        <h1>Shipping Classes</h1>
        <Button onClick={() => openCreateModal()}>Add New Class</Button>
      </div>
      
      <Table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Products</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {classes.map(cls => (
            <tr key={cls.id}>
              <td>{cls.name}</td>
              <td>{cls.description}</td>
              <td>{cls.products_count}</td>
              <td>
                <Button size="sm" onClick={() => editClass(cls)}>Edit</Button>
                <Button 
                  size="sm" 
                  variant="danger" 
                  onClick={() => deleteClass(cls.id)}
                  disabled={cls.products_count > 0}
                >
                  Delete
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </Table>
    </div>
  );
}
```

**Create/Edit Shipping Class Form:**
```tsx
function ShippingClassForm({ shippingClass, onSave, onCancel }: ShippingClassFormProps) {
  const [formData, setFormData] = useState({
    name: shippingClass?.name || '',
    description: shippingClass?.description || ''
  });
  
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    const endpoint = shippingClass 
      ? `/admin/shipping-classes/${shippingClass.id}`
      : '/admin/shipping-classes';
    
    const method = shippingClass ? 'PUT' : 'POST';
    
    const response = await api[method.toLowerCase()](endpoint, formData);
    
    if (response.data.success) {
      onSave(response.data.data);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <FormField label="Name" required>
        <Input
          value={formData.name}
          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          placeholder="e.g., Heavy Equipment"
        />
      </FormField>
      
      <FormField label="Description">
        <Textarea
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          placeholder="Description of this shipping class"
        />
      </FormField>
      
      <div className="form-actions">
        <Button type="submit">Save</Button>
        <Button type="button" variant="secondary" onClick={onCancel}>Cancel</Button>
      </div>
    </form>
  );
}
```

### 3. Product Shipping Configuration

**Product Form - Shipping Section:**
```tsx
function ProductShippingSection({ formData, setFormData, errors }: ProductFormSectionProps) {
  return (
    <div className="product-shipping-section">
      <h3>Shipping Configuration</h3>
      
      <FormField label="Requires Shipping">
        <Checkbox
          checked={formData.requires_shipping}
          onChange={(checked) => setFormData({ ...formData, requires_shipping: checked })}
          label="This product requires shipping"
        />
        <small>Uncheck for digital products or services</small>
      </FormField>
      
      {formData.requires_shipping && (
        <>
          <FormField label="Shipping Type" error={errors.shipping_type}>
            <Select
              value={formData.shipping_type}
              onChange={(value) => setFormData({ ...formData, shipping_type: value })}
            >
              <option value="default">Default (Weight-based)</option>
              <option value="free">Free Shipping</option>
              <option value="fixed">Fixed Cost</option>
              <option value="per_item">Per Item Cost</option>
            </Select>
          </FormField>
          
          {(formData.shipping_type === 'fixed' || formData.shipping_type === 'per_item') && (
            <FormField label="Shipping Cost" required error={errors.shipping_cost}>
              <Input
                type="number"
                step="0.01"
                value={formData.shipping_cost || ''}
                onChange={(e) => setFormData({ 
                  ...formData, 
                  shipping_cost: parseFloat(e.target.value) 
                })}
                placeholder="Enter shipping cost"
              />
              <small>
                {formData.shipping_type === 'fixed' 
                  ? 'Flat rate regardless of quantity'
                  : 'Cost will be multiplied by quantity'
                }
              </small>
            </FormField>
          )}
          
          <FormField label="Shipping Class" error={errors.shipping_class_id}>
            <Select
              value={formData.shipping_class_id || ''}
              onChange={(value) => setFormData({ 
                ...formData, 
                shipping_class_id: value ? parseInt(value) : null 
              })}
            >
              <option value="">No Class</option>
              {shippingClasses.map(cls => (
                <option key={cls.id} value={cls.id}>{cls.name}</option>
              ))}
            </Select>
          </FormField>
          
          <FormField label="Separate Shipping">
            <Checkbox
              checked={formData.separate_shipping}
              onChange={(checked) => setFormData({ ...formData, separate_shipping: checked })}
              label="This product must ship separately"
            />
            <small>Enable for oversized or fragile items</small>
          </FormField>
          
          <FormField label="Shipping Notes" error={errors.shipping_notes}>
            <Textarea
              value={formData.shipping_notes || ''}
              onChange={(e) => setFormData({ ...formData, shipping_notes: e.target.value })}
              placeholder="Special handling instructions (e.g., Fragile, Requires signature)"
              maxLength={500}
            />
          </FormField>
        </>
      )}
    </div>
  );
}
```

**Variation Form - Shipping Override:**
```tsx
function VariationShippingSection({ variation, onChange, errors }: VariationShippingSectionProps) {
  return (
    <div className="variation-shipping">
      <h4>Shipping Override</h4>
      
      <FormField label="Shipping Type" error={errors.shipping_type}>
        <Select
          value={variation.shipping_type || 'inherit'}
          onChange={(value) => onChange({ ...variation, shipping_type: value })}
        >
          <option value="inherit">Inherit from Product</option>
          <option value="free">Free Shipping</option>
          <option value="fixed">Fixed Cost</option>
          <option value="per_item">Per Item Cost</option>
        </Select>
      </FormField>
      
      {(variation.shipping_type === 'fixed' || variation.shipping_type === 'per_item') && (
        <FormField label="Shipping Cost" required error={errors.shipping_cost}>
          <Input
            type="number"
            step="0.01"
            value={variation.shipping_cost || ''}
            onChange={(e) => onChange({ 
              ...variation, 
              shipping_cost: parseFloat(e.target.value) 
            })}
          />
        </FormField>
      )}
    </div>
  );
}
```

---


## UI Components Needed

### 1. Reusable Components

**ShippingBadge Component:**
```tsx
interface ShippingBadgeProps {
  type: 'default' | 'free' | 'fixed' | 'per_item';
  cost?: number;
  requiresShipping?: boolean;
}

function ShippingBadge({ type, cost, requiresShipping = true }: ShippingBadgeProps) {
  if (!requiresShipping) {
    return <Badge color="blue" icon="download">Digital - No Shipping</Badge>;
  }
  
  const badges = {
    free: <Badge color="green" icon="gift">Free Shipping</Badge>,
    fixed: <Badge color="orange" icon="box">Flat ৳{cost}</Badge>,
    per_item: <Badge color="purple" icon="layers">৳{cost}/item</Badge>,
    default: <Badge color="gray" icon="truck">Standard Shipping</Badge>
  };
  
  return badges[type] || badges.default;
}
```

**ShippingCostDisplay Component:**
```tsx
interface ShippingCostDisplayProps {
  cost: number;
  baseCost?: number;
  customCost?: number;
  isFree?: boolean;
}

function ShippingCostDisplay({ 
  cost, 
  baseCost, 
  customCost, 
  isFree 
}: ShippingCostDisplayProps) {
  if (isFree) {
    return <span className="shipping-free">FREE</span>;
  }
  
  return (
    <div className="shipping-cost">
      <span className="total">৳{cost.toFixed(2)}</span>
      {baseCost !== undefined && customCost !== undefined && (
        <div className="breakdown">
          <small>Base: ৳{baseCost.toFixed(2)}</small>
          {customCost > 0 && <small>Custom: ৳{customCost.toFixed(2)}</small>}
        </div>
      )}
    </div>
  );
}
```

**FreeShippingProgress Component:**
```tsx
interface FreeShippingProgressProps {
  currentAmount: number;
  threshold: number;
}

function FreeShippingProgress({ currentAmount, threshold }: FreeShippingProgressProps) {
  const remaining = threshold - currentAmount;
  const progress = (currentAmount / threshold) * 100;
  
  if (remaining <= 0) {
    return (
      <div className="free-shipping-achieved">
        <Icon name="check-circle" color="green" />
        <span>You've qualified for free shipping!</span>
      </div>
    );
  }
  
  return (
    <div className="free-shipping-progress">
      <div className="progress-bar">
        <div className="progress-fill" style={{ width: `${progress}%` }} />
      </div>
      <p>
        Add <strong>৳{remaining.toFixed(2)}</strong> more to get free shipping!
      </p>
    </div>
  );
}
```

**ShippingMethodSelector Component:**
```tsx
interface ShippingMethodSelectorProps {
  methods: ShippingMethod[];
  selected: string | null;
  onSelect: (code: string) => void;
  loading?: boolean;
}

function ShippingMethodSelector({ 
  methods, 
  selected, 
  onSelect, 
  loading 
}: ShippingMethodSelectorProps) {
  if (loading) {
    return <Spinner />;
  }
  
  if (methods.length === 0) {
    return (
      <Alert type="warning">
        No shipping methods available for your location.
      </Alert>
    );
  }
  
  return (
    <div className="shipping-method-selector">
      {methods.map(method => (
        <div
          key={method.code}
          className={`method-option ${selected === method.code ? 'selected' : ''}`}
          onClick={() => onSelect(method.code)}
        >
          <Radio checked={selected === method.code} />
          <div className="method-info">
            <h4>{method.name}</h4>
            <p>{method.description}</p>
            {method.delivery_time && (
              <span className="delivery-time">
                <Icon name="clock" /> {method.delivery_time}
              </span>
            )}
          </div>
          <div className="method-cost">
            <ShippingCostDisplay
              cost={method.cost}
              baseCost={method.base_shipping_cost}
              customCost={method.custom_shipping_cost}
              isFree={method.is_free}
            />
          </div>
        </div>
      ))}
    </div>
  );
}
```

### 2. CSS Styles (Example)

```css
/* Shipping Badge */
.shipping-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.shipping-badge.free {
  background: #d4edda;
  color: #155724;
}

.shipping-badge.fixed {
  background: #fff3cd;
  color: #856404;
}

.shipping-badge.per-item {
  background: #e7d4f7;
  color: #6f42c1;
}

/* Shipping Method Card */
.shipping-method {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.shipping-method:hover {
  border-color: #007bff;
  box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.shipping-method.selected {
  border-color: #007bff;
  background: #f0f8ff;
}

.method-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.method-info {
  flex: 1;
}

.method-info h4 {
  margin: 0 0 4px 0;
  font-size: 16px;
}

.method-info .description {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.delivery-time {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 8px;
  color: #666;
  font-size: 13px;
}

.method-cost {
  text-align: right;
}

.method-cost .total {
  font-size: 20px;
  font-weight: 700;
  color: #333;
}

.method-cost .free {
  font-size: 18px;
  font-weight: 700;
  color: #28a745;
}

.cost-breakdown {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-top: 4px;
}

.cost-breakdown small {
  font-size: 11px;
  color: #999;
}

/* Free Shipping Progress */
.free-shipping-progress {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 16px;
  margin: 16px 0;
}

.progress-bar {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #28a745, #20c997);
  transition: width 0.3s ease;
}

.free-shipping-achieved {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  background: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 8px;
  color: #155724;
}

/* Order Summary */
.order-summary {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.summary-row.total {
  border-bottom: none;
  font-size: 18px;
  font-weight: 700;
  padding-top: 16px;
  border-top: 2px solid #333;
}

.summary-row.discount {
  color: #28a745;
}
```

---

## Implementation Steps

### Phase 1: Customer Features (Priority)

1. **Product Display**
   - [ ] Add shipping badge to product cards
   - [ ] Show shipping info on product detail page
   - [ ] Display shipping notes if present

2. **Cart Page**
   - [ ] Implement shipping method fetching
   - [ ] Display available shipping methods
   - [ ] Show free shipping progress bar
   - [ ] Calculate and display shipping costs

3. **Checkout Flow**
   - [ ] Step 1: Address selection
   - [ ] Step 2: Shipping method selection
   - [ ] Step 3: Order preview with shipping
   - [ ] Step 4: Process order

4. **Order Tracking**
   - [ ] Display shipping method
   - [ ] Show tracking number if available
   - [ ] Order status timeline

### Phase 2: Admin Features

1. **Shipping Rates**
   - [ ] List shipping rates with filters
   - [ ] Create new shipping rate
   - [ ] Edit existing rate
   - [ ] Delete rate
   - [ ] Toggle active/inactive

2. **Shipping Classes**
   - [ ] List shipping classes
   - [ ] Create new class
   - [ ] Edit existing class
   - [ ] Delete class (with validation)

3. **Product Management**
   - [ ] Add shipping section to product form
   - [ ] Shipping type selector
   - [ ] Shipping cost input
   - [ ] Requires shipping checkbox
   - [ ] Separate shipping checkbox
   - [ ] Shipping notes textarea
   - [ ] Shipping class selector

4. **Variation Management**
   - [ ] Add shipping override to variation form
   - [ ] Variation shipping type selector
   - [ ] Variation shipping cost input

### Phase 3: Enhancements

1. **UI/UX Improvements**
   - [ ] Loading states
   - [ ] Error handling
   - [ ] Success messages
   - [ ] Tooltips and help text
   - [ ] Responsive design

2. **Validation**
   - [ ] Client-side validation
   - [ ] Display API errors
   - [ ] Required field indicators

3. **Analytics**
   - [ ] Track shipping method selection
   - [ ] Monitor free shipping threshold effectiveness
   - [ ] Shipping cost analytics

---

## Example API Calls

### Fetch Shipping Methods
```typescript
const fetchShippingMethods = async (
  items: CartItem[], 
  addressId: number, 
  subtotal: number
) => {
  try {
    const response = await axios.post('/api/checkout/shipping-methods', {
      items: items.map(item => ({
        product_id: item.product_id,
        variation_id: item.variation_id,
        quantity: item.quantity
      })),
      address_id: addressId,
      subtotal: subtotal
    });
    
    return Object.values(response.data.data) as ShippingMethod[];
  } catch (error) {
    console.error('Failed to fetch shipping methods:', error);
    throw error;
  }
};
```

### Create Shipping Rate
```typescript
const createShippingRate = async (rateData: Partial<ShippingRate>) => {
  try {
    const response = await axios.post('/api/admin/shipping-rates', rateData);
    return response.data.data;
  } catch (error) {
    if (error.response?.data?.errors) {
      throw error.response.data.errors;
    }
    throw error;
  }
};
```

### Update Product Shipping
```typescript
const updateProductShipping = async (
  productId: number, 
  shippingData: ProductShipping
) => {
  try {
    const response = await axios.put(`/api/admin/products/${productId}`, {
      shipping_type: shippingData.shipping_type,
      shipping_cost: shippingData.shipping_cost,
      requires_shipping: shippingData.requires_shipping,
      separate_shipping: shippingData.separate_shipping,
      shipping_notes: shippingData.shipping_notes,
      shipping_class_id: shippingData.shipping_class_id
    });
    
    return response.data.data;
  } catch (error) {
    console.error('Failed to update product shipping:', error);
    throw error;
  }
};
```

---

## Validation Rules

### Product Shipping Validation

```typescript
const validateProductShipping = (data: ProductShipping) => {
  const errors: Record<string, string> = {};
  
  // Shipping cost required for fixed and per_item types
  if (['fixed', 'per_item'].includes(data.shipping_type)) {
    if (!data.shipping_cost || data.shipping_cost <= 0) {
      errors.shipping_cost = 'Shipping cost is required and must be greater than 0';
    }
  }
  
  // Shipping notes max length
  if (data.shipping_notes && data.shipping_notes.length > 500) {
    errors.shipping_notes = 'Shipping notes must not exceed 500 characters';
  }
  
  return errors;
};
```

### Shipping Rate Validation

```typescript
const validateShippingRate = (data: Partial<ShippingRate>) => {
  const errors: Record<string, string> = {};
  
  if (!data.name || data.name.trim() === '') {
    errors.name = 'Name is required';
  }
  
  if (!data.method) {
    errors.method = 'Method is required';
  }
  
  if (data.base_cost === undefined || data.base_cost < 0) {
    errors.base_cost = 'Base cost is required and must be 0 or greater';
  }
  
  if (data.per_kg_cost !== undefined && data.per_kg_cost < 0) {
    errors.per_kg_cost = 'Per KG cost must be 0 or greater';
  }
  
  if (data.min_weight !== undefined && data.max_weight !== undefined) {
    if (data.min_weight > data.max_weight) {
      errors.max_weight = 'Max weight must be greater than min weight';
    }
  }
  
  return errors;
};
```

---

## Testing Checklist

### Customer Features
- [ ] Product with free shipping shows correct badge
- [ ] Product with fixed shipping displays cost
- [ ] Product with per-item shipping calculates correctly
- [ ] Digital products show "No Shipping" badge
- [ ] Cart calculates shipping for mixed items correctly
- [ ] Free shipping threshold progress bar updates
- [ ] Shipping methods load on address selection
- [ ] Selected shipping method persists through checkout
- [ ] Order preview shows correct shipping cost
- [ ] Order confirmation includes shipping details

### Admin Features
- [ ] Can create shipping rate with all fields
- [ ] Can edit existing shipping rate
- [ ] Can delete shipping rate
- [ ] Can toggle rate active/inactive
- [ ] Can create shipping class
- [ ] Cannot delete class with assigned products
- [ ] Product form saves shipping configuration
- [ ] Variation form saves shipping override
- [ ] Validation errors display correctly
- [ ] Success messages show after save

### Edge Cases
- [ ] Cart with only digital products (no shipping)
- [ ] Cart with only free shipping items
- [ ] Cart with separate shipping items
- [ ] Order exceeding free shipping threshold
- [ ] No shipping methods available for location
- [ ] API errors handled gracefully

---

## Additional Resources

- **Backend Documentation**: See `SHIPPING_API_DOCUMENTATION.md`
- **Custom Shipping Guide**: See `CUSTOM_PRODUCT_SHIPPING.md`
- **Quick Start**: See `SHIPPING_QUICK_START.md`
- **Feature Summary**: See `SHIPPING_FEATURES_SUMMARY.md`

---

## Support & Questions

If you encounter any issues or need clarification:

1. Check the API response format matches the documentation
2. Verify all required fields are being sent
3. Check browser console for errors
4. Review validation rules
5. Test with Postman/Insomnia first to isolate frontend issues

---

**Good luck with the implementation! 🚀**
